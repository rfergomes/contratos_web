<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_can_switch_between_associated_companies(): void
    {
        $company1 = Company::create(['name' => 'Empresa Alpha', 'cnpj' => '11111111000111']);
        $company2 = Company::create(['name' => 'Empresa Omega', 'cnpj' => '22222222000122']);
        $company3 = Company::create(['name' => 'Empresa Secreta', 'cnpj' => '33333333000133']);

        $user = User::create([
            'name' => 'Gestor Teste',
            'email' => 'gestor@teste.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $company1->id,
        ]);

        // Associa o gestor às empresas 1 e 2, mas não à 3
        $user->companies()->attach([$company1->id, $company2->id]);

        $this->actingAs($user);

        // 1. Tenta alternar para a empresa 2 (Omega) - Deve permitir
        $response = $this->post(route('companies.switch'), [
            'company_id' => $company2->id,
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals($company2->id, $user->company_id);

        // 2. Tenta alternar para a empresa 3 (Secreta) - Deve proibir (403)
        $response = $this->post(route('companies.switch'), [
            'company_id' => $company3->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_super_admin_can_switch_to_any_company_or_null(): void
    {
        $company1 = Company::create(['name' => 'Empresa Alpha', 'cnpj' => '11111111000111']);

        $admin = User::create([
            'name' => 'Admin Teste',
            'email' => 'admin@teste.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        $this->actingAs($admin);

        // 1. Alterna para Empresa Alpha
        $response = $this->post(route('companies.switch'), [
            'company_id' => $company1->id,
        ]);

        $response->assertRedirect();
        $admin->refresh();
        $this->assertEquals($company1->id, $admin->company_id);

        // 2. Alterna de volta para nulo (todas)
        $response = $this->post(route('companies.switch'), [
            'company_id' => null,
        ]);

        $response->assertRedirect();
        $admin->refresh();
        $this->assertNull($admin->company_id);
    }
}
