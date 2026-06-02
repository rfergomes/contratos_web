<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\DocumentType;
use App\Models\GedAuditLog;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GedAuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $gestor;

    private User $fornecedor;

    private ContractDocument $document;

    protected function setUp(): void
    {
        parent::setUp();

        $company = Company::create(['name' => 'Empresa Teste', 'cnpj' => '11111111000111']);
        $provider = Provider::create(['name' => 'Fornecedor Teste', 'cnpj' => '22222222000122']);

        $docType = DocumentType::create([
            'name' => 'Comprovante',
            'periodicity' => 'monthly',
            'required' => true,
        ]);

        $contract = Contract::create([
            'company_id' => $company->id,
            'provider_id' => $provider->id,
            'contract_number' => 'CTR-100',
            'title' => 'Contrato Teste',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'signature_validated' => true,
        ]);

        $this->document = ContractDocument::create([
            'contract_id' => $contract->id,
            'document_type_id' => $docType->id,
            'file_path' => 'private/documents/contracts/1/doc.pdf',
            'original_name' => 'doc.pdf',
            'due_date' => '2026-06-15',
            'status' => 'submitted',
        ]);

        $this->gestor = User::create([
            'name' => 'Gestor Teste',
            'email' => 'gestor@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $company->id,
        ]);

        $this->fornecedor = User::create([
            'name' => 'Fornecedor Teste',
            'email' => 'fornecedor@fornecedor.com',
            'password' => bcrypt('password'),
            'role' => 'fornecedor',
            'provider_id' => $provider->id,
        ]);
    }

    /**
     * Testa se a aprovação de um documento cria um registro na tabela ged_audit_logs.
     */
    public function test_approving_document_creates_audit_log(): void
    {
        $this->actingAs($this->gestor);

        // Submete a aprovação
        $response = $this->post("/ged/approve/{$this->document->id}");

        $response->assertStatus(302); // Redirecionamento back()

        // Verifica se o log de auditoria foi gravado
        $this->assertDatabaseHas('ged_audit_logs', [
            'contract_document_id' => $this->document->id,
            'user_id' => $this->gestor->id,
            'action' => 'approved',
        ]);

        $log = GedAuditLog::first();
        $this->assertNotNull($log->metadata);
        $this->assertEquals('127.0.0.1', $log->metadata['ip'] ?? null);
    }

    /**
     * Testa se a recusa falha se a justificativa não for fornecida.
     */
    public function test_rejecting_document_requires_reason(): void
    {
        $this->actingAs($this->gestor);

        $response = $this->post("/ged/reject/{$this->document->id}", [
            'rejection_reason' => '',
        ]);

        $response->assertSessionHasErrors('rejection_reason');
        $this->assertDatabaseCount('ged_audit_logs', 0);
    }

    /**
     * Testa se a recusa de um documento cria um log com justificativa.
     */
    public function test_rejecting_document_creates_audit_log_with_reason(): void
    {
        $this->actingAs($this->gestor);

        $reason = 'Documento ilegível.';
        $response = $this->post("/ged/reject/{$this->document->id}", [
            'rejection_reason' => $reason,
        ]);

        $response->assertStatus(302); // Redirecionamento back()

        $this->assertDatabaseHas('ged_audit_logs', [
            'contract_document_id' => $this->document->id,
            'user_id' => $this->gestor->id,
            'action' => 'rejected',
        ]);

        $log = GedAuditLog::first();
        $this->assertEquals($reason, $log->metadata['reason'] ?? null);
    }

    /**
     * Testa se o histórico de auditoria pode ser recuperado por usuário autorizado.
     */
    public function test_authorized_user_can_fetch_audit_history(): void
    {
        // Cria alguns logs de auditoria
        GedAuditLog::create([
            'contract_document_id' => $this->document->id,
            'user_id' => $this->gestor->id,
            'action' => 'rejected',
            'metadata' => ['ip' => '127.0.0.1', 'user_agent' => 'PHPUnit', 'reason' => 'Erro A'],
        ]);

        GedAuditLog::create([
            'contract_document_id' => $this->document->id,
            'user_id' => $this->gestor->id,
            'action' => 'approved',
            'metadata' => ['ip' => '127.0.0.1', 'user_agent' => 'PHPUnit'],
        ]);

        $this->actingAs($this->gestor);

        $response = $this->getJson("/ged/{$this->document->id}/audit");

        $response->assertStatus(200);
        $response->assertJsonCount(2);

        $response->assertJsonFragment([
            'action' => 'approved',
            'user_name' => $this->gestor->name,
        ]);

        $response->assertJsonFragment([
            'action' => 'rejected',
            'reason' => 'Erro A',
        ]);
    }
}
