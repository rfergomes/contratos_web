<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\ContractRequest;
use App\Models\ContractHistory;
use App\Models\DocumentType;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractRequestTimelineTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $gestorAlpha;
    protected User $fornecedorBeta;
    protected Company $companyAlpha;
    protected Company $companyOmega;
    protected Provider $providerBeta;
    protected Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // Criar empresas
        $this->companyAlpha = Company::create(['name' => 'Alpha LTDA', 'cnpj' => '12.345.678/0001-90', 'active' => true]);
        $this->companyOmega = Company::create(['name' => 'Omega S.A.', 'cnpj' => '98.765.432/0001-10', 'active' => true]);

        // Criar fornecedor
        $this->providerBeta = Provider::create(['name' => 'Beta Serviços', 'cnpj' => '11.222.333/0001-44', 'active' => true]);

        // Criar usuários com perfis
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'active' => true,
        ]);

        $this->gestorAlpha = User::create([
            'name' => 'Gestor Alpha',
            'email' => 'gestor@alpha.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $this->companyAlpha->id,
            'active' => true,
        ]);
        $this->gestorAlpha->companies()->attach($this->companyAlpha->id);

        $this->fornecedorBeta = User::create([
            'name' => 'Fornecedor Beta',
            'email' => 'fornecedor@beta.com',
            'password' => bcrypt('password'),
            'role' => 'fornecedor',
            'provider_id' => $this->providerBeta->id,
            'active' => true,
        ]);

        // Criar contrato
        $this->contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->providerBeta->id,
            'contract_number' => 'CTR-999',
            'title' => 'Serviços de TI',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 30,
            'status' => 'draft',
            'responsible_id' => $this->gestorAlpha->id,
        ]);
    }

    /**
     * Testar se o detalhe do contrato carrega corretamente com as seções do histórico.
     */
    public function test_can_view_contract_details_page()
    {
        // Gestor da empresa do contrato consegue acessar
        $response = $this->actingAs($this->gestorAlpha)->get(route('contracts.show', $this->contract));
        $response->assertStatus(200);
        $response->assertSee('CTR-999');
        $response->assertSee('Serviços de TI');

        // Fornecedor dono do contrato consegue acessar
        $response = $this->actingAs($this->fornecedorBeta)->get(route('contracts.show', $this->contract));
        $response->assertStatus(200);

        // Outro gestor sem permissão de empresa não consegue (Omega)
        $gestorOmega = User::create([
            'name' => 'Gestor Omega',
            'email' => 'gestor@omega.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $this->companyOmega->id,
            'active' => true,
        ]);
        $response = $this->actingAs($gestorOmega)->get(route('contracts.show', $this->contract));
        $response->assertStatus(404);
    }

    /**
     * Testar criação de solicitações bi-direcionais e auto-registro de histórico.
     */
    public function test_can_create_bidirectional_requests_and_logs_history()
    {
        // 1. Fornecedor envia solicitação para a empresa
        $this->actingAs($this->fornecedorBeta)
            ->post(route('contracts.requests.store', $this->contract), [
                'type' => 'amendment',
                'title' => 'Pedido de Reajuste',
                'description' => 'Solicito acréscimo de 10% devido a inflação.',
            ])
            ->assertRedirect();

        // Verificar banco de dados
        $this->assertDatabaseHas('contract_requests', [
            'contract_id' => $this->contract->id,
            'sender_type' => 'provider',
            'type' => 'amendment',
            'title' => 'Pedido de Reajuste',
            'status' => 'pending',
        ]);

        // Verificar se registrou na linha do tempo/histórico
        $this->assertDatabaseHas('contract_histories', [
            'contract_id' => $this->contract->id,
            'action' => 'request_opened',
            'title' => 'Solicitação Aberta: Aditivo Contratual',
        ]);

        // 2. Gestor envia solicitação de esclarecimento para o fornecedor
        $this->actingAs($this->gestorAlpha)
            ->post(route('contracts.requests.store', $this->contract), [
                'type' => 'clarification',
                'title' => 'Dúvida sobre equipe',
                'description' => 'Quantos técnicos estarão alocados?',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('contract_requests', [
            'contract_id' => $this->contract->id,
            'sender_type' => 'company',
            'type' => 'clarification',
            'title' => 'Dúvida sobre equipe',
        ]);
    }

    /**
     * Testar resposta de solicitações (aprovação/rejeição) e controle de permissões.
     */
    public function test_can_respond_to_requests_and_logs_history()
    {
        // Criar uma solicitação aberta pelo fornecedor
        $request = ContractRequest::create([
            'contract_id' => $this->contract->id,
            'sender_type' => 'provider',
            'user_id' => $this->fornecedorBeta->id,
            'type' => 'amendment',
            'title' => 'Pedido de Reajuste',
            'description' => 'Acréscimo salarial.',
            'status' => 'pending',
        ]);

        // Fornecedor tenta aprovar a própria solicitação (não pode!)
        $this->actingAs($this->fornecedorBeta)
            ->post(route('contracts.requests.respond', $request), [
                'status' => 'resolved',
                'response_text' => 'Aprovado por mim mesmo',
            ])
            ->assertStatus(403);

        // Gestor responde resolvendo a solicitação
        $this->actingAs($this->gestorAlpha)
            ->post(route('contracts.requests.respond', $request), [
                'status' => 'resolved',
                'response_text' => 'Deferido conforme solicitação.',
            ])
            ->assertRedirect();

        // Verificar banco de dados
        $this->assertDatabaseHas('contract_requests', [
            'id' => $request->id,
            'status' => 'resolved',
            'response_text' => 'Deferido conforme solicitação.',
            'responded_by' => $this->gestorAlpha->id,
        ]);

        // Verificar log de histórico
        $this->assertDatabaseHas('contract_histories', [
            'contract_id' => $this->contract->id,
            'action' => 'request_resolved',
            'title' => 'Solicitação Resolvida',
        ]);
    }

    /**
     * Testar se as ações de CRUD de contrato e GED disparam registros automáticos no histórico.
     */
    public function test_contract_actions_and_ged_actions_log_history()
    {
        // 1. Log de criação de contrato no controller
        // O setup inicial criou via Eloquent direto. Vamos simular via controller:
        $this->actingAs($this->gestorAlpha)
            ->post(route('contracts.store'), [
                'company_id' => $this->companyAlpha->id,
                'provider_id' => $this->providerBeta->id,
                'responsible_id' => $this->gestorAlpha->id,
                'contract_number' => 'CTR-1002',
                'title' => 'Novo Contrato de Suporte',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'alert_days' => 15,
            ])
            ->assertRedirect();

        $newContract = Contract::where('contract_number', 'CTR-1002')->first();
        $this->assertNotNull($newContract);

        $this->assertDatabaseHas('contract_histories', [
            'contract_id' => $newContract->id,
            'action' => 'created',
            'title' => 'Contrato Criado',
        ]);

        // 2. Log de upload de documento no GED
        $docType = DocumentType::create(['name' => 'Alvará', 'periodicity' => 'annual', 'required' => true]);
        $document = ContractDocument::create([
            'contract_id' => $this->contract->id,
            'document_type_id' => $docType->id,
            'due_date' => now()->addMonth(),
            'status' => 'pending',
        ]);

        $file = UploadedFile::fake()->create('alvara.pdf', 500);

        $this->actingAs($this->fornecedorBeta)
            ->post(route('ged.upload', $document), ['file' => $file])
            ->assertRedirect();

        $this->assertDatabaseHas('contract_histories', [
            'contract_id' => $this->contract->id,
            'action' => 'document_submitted',
            'title' => 'Documento Enviado',
        ]);

        // 3. Log de aprovação de documento no GED
        $this->actingAs($this->gestorAlpha)
            ->post(route('ged.approve', $document))
            ->assertRedirect();

        $this->assertDatabaseHas('contract_histories', [
            'contract_id' => $this->contract->id,
            'action' => 'document_approved',
            'title' => 'Documento Aprovado',
        ]);
    }
}
