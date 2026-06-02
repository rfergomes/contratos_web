<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Provider;
use App\Models\ProviderContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderContactsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $gestorAlpha;

    protected User $fornecedorBeta;

    protected Company $companyAlpha;

    protected Provider $providerBeta;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar empresa
        $this->companyAlpha = Company::create(['name' => 'Alpha LTDA', 'cnpj' => '12.345.678/0001-90', 'active' => true]);

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
    }

    /**
     * Teste: Fornecedores não podem gerenciar contatos de fornecedores.
     */
    public function test_fornecedor_cannot_access_contacts_endpoints(): void
    {
        $this->actingAs($this->fornecedorBeta);

        // Tentar listar
        $response = $this->get(route('providers.contacts.index', $this->providerBeta));
        $response->assertStatus(403);

        // Tentar criar
        $response = $this->post(route('providers.contacts.store', $this->providerBeta), [
            'name' => 'Novo Contato',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Teste: Gestores podem listar contatos de fornecedores.
     */
    public function test_gestor_can_list_contacts(): void
    {
        $this->actingAs($this->gestorAlpha);

        $contact1 = ProviderContact::create([
            'provider_id' => $this->providerBeta->id,
            'name' => 'Contato Um',
            'phone' => '11999999999',
            'email' => 'um@beta.com',
            'is_main' => false,
        ]);

        $contact2 = ProviderContact::create([
            'provider_id' => $this->providerBeta->id,
            'name' => 'Contato Dois Principal',
            'phone' => '11888888888',
            'email' => 'dois@beta.com',
            'is_main' => true,
        ]);

        $response = $this->get(route('providers.contacts.index', $this->providerBeta));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(2, $data);

        // Deve vir ordenado por is_main desc (o contato 2 deve ser o primeiro da lista)
        $this->assertEquals($contact2->id, $data[0]['id']);
        $this->assertEquals($contact1->id, $data[1]['id']);
    }

    /**
     * Teste: Adição de contato de fornecedor.
     */
    public function test_gestor_can_create_provider_contact(): void
    {
        $this->actingAs($this->gestorAlpha);

        $response = $this->post(route('providers.contacts.store', $this->providerBeta), [
            'name' => 'João Silva',
            'phone' => '19987654321',
            'email' => 'joao@beta.com',
            'is_main' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('provider_contacts', [
            'provider_id' => $this->providerBeta->id,
            'name' => 'João Silva',
            'is_main' => true,
        ]);
    }

    /**
     * Teste: Edição de contato de fornecedor.
     */
    public function test_gestor_can_update_provider_contact(): void
    {
        $this->actingAs($this->gestorAlpha);

        $contact = ProviderContact::create([
            'provider_id' => $this->providerBeta->id,
            'name' => 'João Silva Original',
            'phone' => '19987654321',
            'email' => 'joao@beta.com',
            'is_main' => false,
        ]);

        $response = $this->put(route('providers.contacts.update', $contact), [
            'name' => 'João Silva Alterado',
            'phone' => '19999999999',
            'email' => 'joao.novo@beta.com',
            'is_main' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('provider_contacts', [
            'id' => $contact->id,
            'name' => 'João Silva Alterado',
            'phone' => '19999999999',
        ]);
    }

    /**
     * Teste: Exclusão de contato de fornecedor.
     */
    public function test_gestor_can_delete_provider_contact(): void
    {
        $this->actingAs($this->gestorAlpha);

        $contact = ProviderContact::create([
            'provider_id' => $this->providerBeta->id,
            'name' => 'João Silva',
            'phone' => '19987654321',
            'email' => 'joao@beta.com',
            'is_main' => false,
        ]);

        $response = $this->delete(route('providers.contacts.destroy', $contact));
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('provider_contacts', [
            'id' => $contact->id,
        ]);
    }

    /**
     * Teste: Regra de contato principal único por fornecedor.
     */
    public function test_only_one_contact_can_be_main_per_provider(): void
    {
        $this->actingAs($this->gestorAlpha);

        $contact1 = ProviderContact::create([
            'provider_id' => $this->providerBeta->id,
            'name' => 'Contato 1 Principal',
            'is_main' => true,
        ]);

        $this->assertTrue($contact1->fresh()->is_main);

        // Criar contato 2 como principal via API
        $response = $this->post(route('providers.contacts.store', $this->providerBeta), [
            'name' => 'Contato 2 Novo Principal',
            'is_main' => true,
        ]);

        $response->assertStatus(200);

        // Contato 1 deve deixar de ser principal
        $this->assertFalse($contact1->fresh()->is_main);

        // Contato 2 deve ser principal
        $contact2 = ProviderContact::where('name', 'Contato 2 Novo Principal')->first();
        $this->assertTrue($contact2->is_main);

        // Toggles via endpoint toggleMain
        $response = $this->patch(route('providers.contacts.toggle-main', $contact1));
        $response->assertStatus(200);

        // Contato 1 deve ser principal novamente
        $this->assertTrue($contact1->fresh()->is_main);
        // Contato 2 deve deixar de ser principal
        $this->assertFalse($contact2->fresh()->is_main);
    }
}
