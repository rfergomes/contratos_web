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

class DashboardAlertsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $gestorAlpha;
    protected User $fornecedorBeta;
    protected Company $companyAlpha;
    protected Provider $providerBeta;
    protected Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyAlpha = Company::create(['name' => 'Alpha LTDA', 'cnpj' => '12.345.678/0001-90', 'active' => true]);
        $this->providerBeta = Provider::create(['name' => 'Beta Serviços', 'cnpj' => '11.222.333/0001-44', 'active' => true]);

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

        $this->contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->providerBeta->id,
            'contract_number' => 'CTR-999',
            'title' => 'Serviços de TI',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 30,
            'status' => 'active',
            'responsible_id' => $this->gestorAlpha->id,
        ]);
    }

    /**
     * Teste: Super admin acessa dashboard. Não gera alertas automáticos.
     */
    public function test_super_admin_dashboard_access(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Empresas Contratantes');
    }

    /**
     * Teste: Geração dinâmica de alertas de prazos de documentos e solicitações ao acessar o Dashboard.
     */
    public function test_dashboard_access_generates_deadline_alerts_for_gestor_and_provider(): void
    {
        // 1. Criar uma solicitação pendente próxima do vencimento (due_date em 3 dias) enviada pelo fornecedor.
        // O destinatário de responder é a empresa (Gestor).
        $request = ContractRequest::create([
            'contract_id' => $this->contract->id,
            'sender_type' => 'provider',
            'user_id' => $this->fornecedorBeta->id,
            'type' => 'amendment',
            'title' => 'Pedido de Reajuste Urgente',
            'description' => 'Acréscimo salarial.',
            'status' => 'pending',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        // 2. Criar uma obrigação do GED pendente próxima do vencimento (due_date em 4 dias)
        $docType = DocumentType::create([
            'name' => 'Certidão Negativa Tributária',
            'periodicity' => 'monthly',
            'required' => true,
        ]);

        $doc = ContractDocument::create([
            'contract_id' => $this->contract->id,
            'document_type_id' => $docType->id,
            'status' => 'pending',
            'due_date' => now()->addDays(4)->toDateString(),
        ]);

        // Acessar como gestor: deve gerar os alertas e mostrá-los
        $this->actingAs($this->gestorAlpha);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Deve ter gerado alertas no BD
        $this->assertDatabaseHas('alerts', [
            'user_id' => $this->gestorAlpha->id,
            'source_type' => 'ContractRequest',
            'source_id' => $request->id,
            'type' => 'request_deadline',
        ]);

        $this->assertDatabaseHas('alerts', [
            'user_id' => $this->gestorAlpha->id,
            'source_type' => 'ContractDocument',
            'source_id' => $doc->id,
            'type' => 'obligation_deadline',
        ]);

        $response->assertSee('Prazo de Solicitação Próximo');
        $response->assertSee('Vencimento de Documento');
    }

    /**
     * Teste: Criação automática de alerta ao submeter nova solicitação.
     */
    public function test_automatic_alert_on_new_request(): void
    {
        // 1. Fornecedor submete solicitação para a empresa
        $this->actingAs($this->fornecedorBeta)
            ->post(route('contracts.requests.store', $this->contract), [
                'type' => 'amendment',
                'title' => 'Pedido de Aditivo',
                'description' => 'Descrição do aditivo.',
            ])
            ->assertRedirect();

        // Deve existir alerta para o gestor responsável pelo contrato
        $this->assertDatabaseHas('alerts', [
            'user_id' => $this->gestorAlpha->id,
            'title' => 'Nova Solicitação Recebida',
            'type' => 'new_request',
        ]);
    }

    /**
     * Teste: Criação automática de alerta ao responder solicitação.
     */
    public function test_automatic_alert_on_request_response(): void
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

        // Gestor responde resolvendo a solicitação
        $this->actingAs($this->gestorAlpha)
            ->post(route('contracts.requests.respond', $request), [
                'status' => 'resolved',
                'response_text' => 'Aprovado.',
            ])
            ->assertRedirect();

        // Deve existir alerta de resposta para o criador da solicitação (fornecedorBeta)
        $this->assertDatabaseHas('alerts', [
            'user_id' => $this->fornecedorBeta->id,
            'title' => 'Solicitação Respondida',
            'type' => 'request_response',
        ]);
    }

    /**
     * Teste: Endpoint de marcar alerta como lido (e validação de segurança).
     */
    public function test_mark_alert_as_read_and_security(): void
    {
        // 1. Criar um alerta para o gestorAlpha
        $alert = Alert::create([
            'user_id' => $this->gestorAlpha->id,
            'title' => 'Alerta do Gestor',
            'message' => 'Alguma mensagem.',
            'link' => '#',
            'type' => 'new_request',
        ]);

        // Outro usuário (fornecedor) tenta marcar como lido (deve dar 403)
        $this->actingAs($this->fornecedorBeta);
        $response = $this->patchJson(route('alerts.read', $alert));
        $response->assertStatus(403);

        // O próprio usuário (gestorAlpha) marca como lido (deve dar 200/JSON)
        $this->actingAs($this->gestorAlpha);
        $response = $this->patchJson(route('alerts.read', $alert));
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertNotNull($alert->fresh()->read_at);
    }

    /**
     * Teste: Redirecionador do alerta marcando-o como lido.
     */
    public function test_navigate_alert_marks_as_read_and_redirects(): void
    {
        $alert = Alert::create([
            'user_id' => $this->gestorAlpha->id,
            'title' => 'Alerta do Gestor',
            'message' => 'Alguma mensagem.',
            'link' => 'http://localhost:8000/contracts/1',
            'type' => 'new_request',
        ]);

        $this->actingAs($this->gestorAlpha);
        $response = $this->get(route('alerts.go', $alert));
        
        $response->assertRedirect('http://localhost:8000/contracts/1');
        $this->assertNotNull($alert->fresh()->read_at);
    }
}
