<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\DocumentType;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $gestor;

    protected Company $company;

    protected Provider $provider;

    protected DocumentType $docType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Empresa Teste', 'cnpj' => '11111111000111', 'active' => true]);
        $this->provider = Provider::create(['name' => 'Fornecedor Teste', 'cnpj' => '22222222000122', 'active' => true]);

        $this->gestor = User::create([
            'name' => 'Gestor Teste',
            'email' => 'gestor@teste.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $this->company->id,
            'active' => true,
        ]);
        $this->gestor->companies()->attach($this->company->id);

        $this->docType = DocumentType::create([
            'name' => 'Alvará',
            'periodicity' => 'annual',
            'required' => true,
        ]);
    }

    /**
     * Teste: Contrato nasce com status 'pending' ao ser cadastrado se houver obrigações ou se a assinatura não for validada.
     */
    public function test_contract_initial_status_is_pending_after_registration(): void
    {
        $this->actingAs($this->gestor);

        $response = $this->post(route('contracts.store'), [
            'company_id' => $this->company->id,
            'provider_id' => $this->provider->id,
            'contract_number' => 'CTR-LIFECYCLE-1',
            'title' => 'Contrato de Teste Lifecycle',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 30,
            'document_types' => [$this->docType->id],
        ]);

        $response->assertRedirect();

        $contract = Contract::where('contract_number', 'CTR-LIFECYCLE-1')->first();
        $this->assertNotNull($contract);

        // Deve começar com status 'pending'
        $this->assertEquals('pending', $contract->status);
        $this->assertFalse($contract->signature_validated);

        // Deve ter gerado a obrigação documental
        $this->assertEquals(1, $contract->documents()->count());
    }

    /**
     * Teste: Contrato move-se para 'active' apenas quando todas as obrigações forem aprovadas E a assinatura confirmada.
     */
    public function test_contract_lifecycle_flow_to_active(): void
    {
        $contract = Contract::create([
            'company_id' => $this->company->id,
            'provider_id' => $this->provider->id,
            'contract_number' => 'CTR-FLOW',
            'title' => 'Contrato Fluxo',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 30,
            'status' => 'pending',
            'signature_validated' => false,
        ]);

        $doc = ContractDocument::create([
            'contract_id' => $contract->id,
            'document_type_id' => $this->docType->id,
            'due_date' => now()->addDays(15),
            'status' => 'pending',
        ]);

        // 1. Mesmo validando a assinatura, o status continua 'pending' pois o documento está pendente
        $this->actingAs($this->gestor)->post(route('contracts.validate-signature', $contract));
        $contract->refresh();
        $this->assertTrue($contract->signature_validated);
        $this->assertEquals('pending', $contract->status);

        // 2. Simulamos a aprovação do documento
        $doc->update([
            'status' => 'approved',
            'approved_at' => now(),
            'reviewed_by' => $this->gestor->id,
        ]);

        // Agora sem pendências documentais E assinatura validada, o status deve ir para 'active' automaticamente
        $contract->refresh();
        $this->assertEquals('active', $contract->status);

        // 3. Adicionar uma nova obrigação documental pendente deve reverter o contrato para 'pending'
        $this->post(route('contracts.documents.store', $contract), [
            'document_type_id' => $this->docType->id,
            'due_date' => now()->addDays(30)->toDateString(),
        ]);

        $contract->refresh();
        $this->assertEquals('pending', $contract->status);
    }

    /**
     * Teste: Gerar link de WhatsApp registra a cobrança no histórico do contrato.
     */
    public function test_generating_whatsapp_link_registers_whatsapp_charge_in_contract_history(): void
    {
        $contract = Contract::create([
            'company_id' => $this->company->id,
            'provider_id' => $this->provider->id,
            'contract_number' => 'CTR-WA',
            'title' => 'Contrato WhatsApp',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 30,
            'status' => 'pending',
            'signature_validated' => false,
        ]);

        $doc = ContractDocument::create([
            'contract_id' => $contract->id,
            'document_type_id' => $this->docType->id,
            'due_date' => now()->addDays(15),
            'status' => 'pending',
        ]);

        $this->actingAs($this->gestor);

        // Gera o link do WhatsApp para o documento exigido
        $response = $this->postJson(route('contracts.documents.whatsapp-link', $doc));
        $response->assertStatus(200);

        // Deve registrar a ação 'whatsapp_charge' no histórico do contrato
        $this->assertDatabaseHas('contract_histories', [
            'contract_id' => $contract->id,
            'action' => 'whatsapp_charge',
            'title' => 'Cobrança via WhatsApp',
        ]);
    }
}
