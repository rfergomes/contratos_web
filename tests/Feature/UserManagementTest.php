<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste: Fornecedores não podem gerenciar usuários.
     */
    public function test_supplier_cannot_access_user_management(): void
    {
        $supplier = User::create([
            'name' => 'Preposto Beta',
            'email' => 'preposto@beta.com',
            'password' => bcrypt('password'),
            'role' => 'fornecedor',
        ]);

        $this->actingAs($supplier);

        $response = $this->get(route('users.index'));
        $response->assertStatus(403);
    }

    /**
     * Teste: Gestor pode listar apenas usuários de sua empresa.
     */
    public function test_gestor_lists_only_users_from_their_own_company(): void
    {
        $company1 = Company::create(['name' => 'Empresa Alpha', 'cnpj' => '11111111000111']);
        $company2 = Company::create(['name' => 'Empresa Omega', 'cnpj' => '22222222000122']);

        $gestor = User::create([
            'name' => 'Gestor Alpha',
            'email' => 'gestor@alpha.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $company1->id,
        ]);

        $otherUserInCompany1 = User::create([
            'name' => 'Usuario Interno Alpha',
            'email' => 'interno@alpha.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $company1->id,
        ]);

        $userInCompany2 = User::create([
            'name' => 'Usuario Omega',
            'email' => 'usuario@omega.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $company2->id,
        ]);

        $this->actingAs($gestor);

        $response = $this->get(route('users.index'));
        $response->assertStatus(200);

        // Deve conter os usuários da empresa Alpha
        $response->assertSee('Usuario Interno Alpha');

        // NÃO deve conter o usuário da empresa Omega
        $response->assertDontSee('Usuario Omega');
    }

    /**
     * Teste: Gestor não pode criar usuários do tipo super_admin.
     */
    public function test_gestor_cannot_create_super_admin_users(): void
    {
        $company = Company::create(['name' => 'Empresa Alpha', 'cnpj' => '11111111000111']);

        $gestor = User::create([
            'name' => 'Gestor Alpha',
            'email' => 'gestor@alpha.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $company->id,
        ]);

        $this->actingAs($gestor);

        $response = $this->post(route('users.store'), [
            'name' => 'Invasor Admin',
            'email' => 'invasor@admin.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'company_id' => $company->id,
        ]);

        // Validação deve falhar no campo 'role' (super_admin não permitido para gestor)
        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'invasor@admin.com']);
    }
}
