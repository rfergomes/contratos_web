<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\ContractRequest;
use App\Models\DocumentType;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalContractsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $gestorAlpha;

    protected User $fornecedorBeta;

    protected Company $companyAlpha;

    protected Provider $providerBeta;

    protected Provider $internalProviderAlpha;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyAlpha = Company::create(['name' => 'Alpha LTDA', 'cnpj' => '12.345.678/0001-90', 'active' => true]);
        $this->providerBeta = Provider::create(['name' => 'Beta Serviços', 'cnpj' => '11.222.333/0001-44', 'active' => true]);

        // Categoria Controle Interno
        $this->internalProviderAlpha = Provider::create([
            'name' => 'Controle Interno - Alpha LTDA',
            'cnpj' => null,
            'active' => true,
        ]);

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
    }

    /**
     * Teste: Um gestor pode criar um contrato sem associar um fornecedor (controle interno).
     */
    public function test_gestor_can_create_internal_contract(): void
    {
        $this->actingAs($this->gestorAlpha);

        $response = $this->post(route('contracts.store'), [
            'company_id' => $this->companyAlpha->id,
            'management_type' => 'internal',
            'provider_id' => '', // Vazio / Controle Interno
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-INT-001',
            'title' => 'Contrato Interno de Internet',
            'description' => 'Link dedicado de internet de contingência.',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 30,
        ]);

        $response->assertRedirect(route('contracts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contracts', [
            'contract_number' => 'CTR-INT-001',
            'management_type' => 'internal',
        ]);

        $contract = Contract::where('contract_number', 'CTR-INT-001')->first();
        $this->assertNotNull($contract->provider_id);
        $this->assertEquals($this->internalProviderAlpha->id, $contract->provider_id);
    }

    /**
     * Teste: Acessar a página de detalhes de um contrato interno funciona normalmente.
     */
    public function test_view_internal_contract_details_works(): void
    {
        $contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->internalProviderAlpha->id,
            'management_type' => 'internal',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-INT-002',
            'title' => 'Contrato Interno de Telefonia',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        $this->actingAs($this->gestorAlpha);

        $response = $this->get(route('contracts.show', $contract));
        $response->assertStatus(200);
        $response->assertSee('Controle Interno');
        $response->assertSee('Uso Interno');
    }

    /**
     * Teste: Geração de link do WhatsApp para solicitação de contrato interno deve falhar com HTTP 422.
     */
    public function test_whatsapp_link_generation_aborts_for_internal_contract_requests(): void
    {
        $contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->internalProviderAlpha->id,
            'management_type' => 'internal',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-INT-003',
            'title' => 'Contrato Interno Telecom',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        $request = ContractRequest::create([
            'contract_id' => $contract->id,
            'sender_type' => 'company',
            'user_id' => $this->gestorAlpha->id,
            'type' => 'clarification',
            'title' => 'Dúvida Interna',
            'description' => 'Apenas um lembrete interno.',
            'status' => 'pending',
        ]);

        $this->actingAs($this->gestorAlpha);

        $response = $this->post(route('contracts.requests.whatsapp-link', $request));
        $response->assertStatus(422);
    }

    /**
     * Teste: Geração de link do WhatsApp para documento de contrato interno deve falhar com HTTP 422.
     */
    public function test_whatsapp_link_generation_aborts_for_internal_contract_documents(): void
    {
        $contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->internalProviderAlpha->id,
            'management_type' => 'internal',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-INT-004',
            'title' => 'Contrato Interno Limpeza',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        $docType = DocumentType::create([
            'name' => 'Alvará de Funcionamento',
            'periodicity' => 'once',
            'active' => true,
        ]);

        $document = ContractDocument::create([
            'contract_id' => $contract->id,
            'document_type_id' => $docType->id,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($this->gestorAlpha);

        $response = $this->post(route('contracts.documents.whatsapp-link', $document));
        $response->assertStatus(422);
    }

    /**
     * Teste: Criação de nova solicitação para contrato interno não deve enviar alertas a todos os gestores/admins.
     */
    public function test_alert_creation_does_not_alert_all_users_for_internal_contract_request(): void
    {
        $contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->internalProviderAlpha->id,
            'management_type' => 'internal',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-INT-005',
            'title' => 'Contrato Interno Segurança',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        // Certifica de que não há outros alertas
        Alert::truncate();

        ContractRequest::create([
            'contract_id' => $contract->id,
            'sender_type' => 'company',
            'user_id' => $this->gestorAlpha->id,
            'type' => 'clarification',
            'title' => 'Apenas Lembrete',
            'description' => 'Apenas um lembrete interno.',
            'status' => 'pending',
        ]);

        // Como o contrato é interno (management_type = internal), nenhum alerta de nova solicitação
        // deve ser disparado, pois não há fornecedor.
        $this->assertEquals(0, Alert::count());
    }

    /**
     * Teste: Gestor visualiza tanto contratos com fornecedor quanto contratos internos na listagem.
     */
    public function test_gestor_sees_both_internal_and_provider_contracts_on_list(): void
    {
        // Contrato Interno
        Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->internalProviderAlpha->id,
            'management_type' => 'internal',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-LIST-INT',
            'title' => 'Contrato Interno Telecom',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        // Contrato Com Fornecedor
        Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->providerBeta->id,
            'management_type' => 'external',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-LIST-PROV',
            'title' => 'Contrato de Fornecimento',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        $this->actingAs($this->gestorAlpha);

        $response = $this->get(route('contracts.index'));
        $response->assertStatus(200);
        $response->assertSee('CTR-LIST-INT');
        $response->assertSee('CTR-LIST-PROV');
    }

    /**
     * Teste: Fornecedor visualiza apenas contratos que lhe pertencem e não contratos internos.
     */
    public function test_fornecedor_only_sees_their_contracts_on_list(): void
    {
        // Outro Fornecedor
        $otherProvider = Provider::create(['name' => 'Gama Tech', 'cnpj' => '99.888.777/0001-66', 'active' => true]);

        // Contrato de outro fornecedor
        Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $otherProvider->id,
            'management_type' => 'external',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-LIST-GAMA',
            'title' => 'Contrato Gama Tech',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        // Contrato Interno
        Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->internalProviderAlpha->id,
            'management_type' => 'internal',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-LIST-INT',
            'title' => 'Contrato Interno Telecom',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        // Contrato de fornecedor Beta (do usuário fornecedor Beta)
        Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->providerBeta->id,
            'management_type' => 'external',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-LIST-BETA',
            'title' => 'Contrato Beta Telecom',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        $this->actingAs($this->fornecedorBeta);

        $response = $this->get(route('contracts.index'));
        $response->assertStatus(200);
        $response->assertSee('CTR-LIST-BETA');
        $response->assertDontSee('CTR-LIST-INT');
        $response->assertDontSee('CTR-LIST-GAMA');
    }

    /**
     * Teste: Contratos internos rejeitam a abertura de solicitações.
     */
    public function test_internal_contract_blocks_request_creation(): void
    {
        $contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->internalProviderAlpha->id,
            'management_type' => 'internal',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-INT-LIMIT',
            'title' => 'Contrato Interno Limitado',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        $this->actingAs($this->gestorAlpha);

        $response = $this->post(route('contracts.requests.store', $contract), [
            'type' => 'clarification',
            'title' => 'Tentativa de Solicitação',
            'description' => 'Isso não deve ser aceito.',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Teste: Gestor pode registrar evento de histórico manual.
     */
    public function test_gestor_can_log_manual_history_entry(): void
    {
        $contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->providerBeta->id,
            'management_type' => 'external',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-HIST-001',
            'title' => 'Contrato com Histórico',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        $this->actingAs($this->gestorAlpha);

        $response = $this->post(route('contracts.history.store', $contract), [
            'title' => 'Reunião Realizada',
            'description' => 'Alinhamento geral sobre a entrega do próximo lote.',
            'action_type' => 'communication',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contract_histories', [
            'contract_id' => $contract->id,
            'action' => 'communication',
            'title' => 'Reunião Realizada',
            'description' => 'Alinhamento geral sobre a entrega do próximo lote.',
            'user_id' => $this->gestorAlpha->id,
        ]);
    }

    /**
     * Teste: Fornecedor não pode registrar evento no histórico.
     */
    public function test_fornecedor_cannot_log_manual_history(): void
    {
        $contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->providerBeta->id,
            'management_type' => 'external',
            'responsible_id' => $this->gestorAlpha->id,
            'contract_number' => 'CTR-HIST-002',
            'title' => 'Contrato com Histórico Fornecedor',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 15,
            'status' => 'active',
        ]);

        $this->actingAs($this->fornecedorBeta);

        $response = $this->post(route('contracts.history.store', $contract), [
            'title' => 'Tentativa de Fornecedor',
            'description' => 'Não deve registrar.',
            'action_type' => 'manual_note',
        ]);

        $response->assertStatus(403);
    }
}
