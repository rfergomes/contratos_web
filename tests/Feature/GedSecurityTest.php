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

class GedSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se usuários não autenticados são redirecionados para o login.
     */
    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/ged');
        $response->assertRedirect('/login');
    }

    /**
     * Testa se um fornecedor é proibido de baixar documentos de outro fornecedor.
     */
    public function test_supplier_cannot_download_other_suppliers_document(): void
    {
        // Cria empresas, fornecedores e tipos de documentos
        $company = Company::create(['name' => 'Empresa A', 'cnpj' => '11111111000111']);
        $provider1 = Provider::create(['name' => 'Fornecedor 1', 'cnpj' => '22222222000122']);
        $provider2 = Provider::create(['name' => 'Fornecedor 2', 'cnpj' => '33333333000133']);

        $docType = DocumentType::create([
            'name' => 'FGTS',
            'periodicity' => 'monthly',
            'required' => true,
        ]);

        // Contratos distintos
        $contract1 = Contract::create([
            'company_id' => $company->id,
            'provider_id' => $provider1->id,
            'contract_number' => 'CTR-1',
            'title' => 'Contrato 1',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'signature_validated' => true,
        ]);

        $contract2 = Contract::create([
            'company_id' => $company->id,
            'provider_id' => $provider2->id,
            'contract_number' => 'CTR-2',
            'title' => 'Contrato 2',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'signature_validated' => true,
        ]);

        // Documento do Contrato 1
        $document = ContractDocument::create([
            'contract_id' => $contract1->id,
            'document_type_id' => $docType->id,
            'file_path' => 'private/documents/contracts/1/fgts.pdf',
            'original_name' => 'fgts.pdf',
            'due_date' => '2026-06-15',
            'status' => 'submitted',
        ]);

        // Cria usuários vinculados a cada fornecedor
        $user1 = User::create([
            'name' => 'User Fornecedor 1',
            'email' => 'user1@fornecedor1.com',
            'password' => bcrypt('password'),
            'role' => 'fornecedor',
            'provider_id' => $provider1->id,
        ]);

        $user2 = User::create([
            'name' => 'User Fornecedor 2',
            'email' => 'user2@fornecedor2.com',
            'password' => bcrypt('password'),
            'role' => 'fornecedor',
            'provider_id' => $provider2->id,
        ]);

        // Autentica como Usuário 2 e tenta baixar documento do Contrato 1 (Fornecedor 1)
        $this->actingAs($user2);

        $response = $this->get("/ged/download/{$document->id}");

        // Deve retornar 404 Not Found porque o DocumentScope oculta o registro
        $response->assertStatus(404);
    }
}
