<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTypeTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $gestor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'active' => true,
        ]);

        $this->gestor = User::create([
            'name' => 'Gestor Alpha',
            'email' => 'gestor@alpha.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'active' => true,
        ]);
    }

    /**
     * Teste: Apenas Super Admin pode gerenciar tipos de documentos.
     */
    public function test_only_super_admin_can_access_document_types(): void
    {
        // Gestor tenta acessar e é barrado
        $this->actingAs($this->gestor);
        $response = $this->get(route('document-types.index'));
        $response->assertStatus(403);

        // Super Admin consegue acessar
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('document-types.index'));
        $response->assertStatus(200);
    }

    /**
     * Teste: Super Admin pode criar tipo de documento.
     */
    public function test_super_admin_can_create_document_type(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('document-types.store'), [
            'name' => 'Contrato Social',
            'description' => 'Documento de constituição da empresa',
            'periodicity' => 'once',
            'required' => 1,
        ]);

        $response->assertRedirect(route('document-types.index'));
        $this->assertDatabaseHas('document_types', [
            'name' => 'Contrato Social',
            'periodicity' => 'once',
            'required' => true,
        ]);
    }

    /**
     * Teste: Super Admin pode editar tipo de documento.
     */
    public function test_super_admin_can_update_document_type(): void
    {
        $this->actingAs($this->superAdmin);

        $docType = DocumentType::create([
            'name' => 'Alvará Original',
            'periodicity' => 'annual',
            'required' => false,
        ]);

        $response = $this->put(route('document-types.update', $docType), [
            'name' => 'Alvará Atualizado',
            'description' => 'Descrição atualizada',
            'periodicity' => 'monthly',
            'required' => 1,
        ]);

        $response->assertRedirect(route('document-types.index'));
        $this->assertDatabaseHas('document_types', [
            'id' => $docType->id,
            'name' => 'Alvará Atualizado',
            'periodicity' => 'monthly',
            'required' => true,
        ]);
    }

    /**
     * Teste: Super Admin pode alternar obrigatoriedade.
     */
    public function test_super_admin_can_toggle_required(): void
    {
        $this->actingAs($this->superAdmin);

        $docType = DocumentType::create([
            'name' => 'Alvará',
            'periodicity' => 'annual',
            'required' => false,
        ]);

        $response = $this->patch(route('document-types.toggle', $docType));
        $response->assertRedirect(route('document-types.index'));

        $this->assertTrue($docType->fresh()->required);
    }

    /**
     * Teste: Os novos tipos de documentos expandidos são populados pela migration.
     */
    public function test_expanded_document_types_are_seeded_by_migration(): void
    {
        $expectedNames = [
            'Nota Fiscal de Serviços Eletrônica (NFS-e)',
            'Recibo de Pagamento Autônomo (RPA)',
            'Guia e Comprovante do Simples Nacional (DAS)',
            'Guia e Comprovante de Tributos Federais (DARF/DARM)',
            'Recibo de Entrega da DCTFWeb / EFD-Reinf',
            'Recibo de Envio do eSocial',
            'Extrato do PGDAS-D',
            'Declaração do Simples Nacional (DEFIS)',
            'Certidão Negativa de Débitos Estaduais (CND Estadual)',
            'Certidão Negativa de Débitos Municipais (CND Municipal)',
            'Folha de Pagamento e Encargos Sociais (GFIP/SEFIP/FGTS)',
        ];

        foreach ($expectedNames as $name) {
            $this->assertDatabaseHas('document_types', [
                'name' => $name,
            ]);
        }
    }
}
