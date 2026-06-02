<?php

namespace Tests\Feature;

use App\Mail\ContractExpiryAlert;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContractExpiryAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_contracts_expiry_alerts_are_sent_correctly(): void
    {
        Mail::fake();

        $company = Company::create(['name' => 'Empresa A', 'cnpj' => '11111111000111']);
        $provider = Provider::create(['name' => 'Fornecedor 1', 'cnpj' => '22222222000122']);

        $responsible = User::create([
            'name' => 'Gestor Responsavel',
            'email' => 'responsavel@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $company->id,
        ]);

        // Contrato 1: Vence em 30 dias com alerta de 30 dias (deve disparar hoje!)
        $contractTriggering = Contract::create([
            'company_id' => $company->id,
            'provider_id' => $provider->id,
            'responsible_id' => $responsible->id,
            'contract_number' => 'CTR-TRIGGER',
            'title' => 'Contrato Alerta Hoje',
            'start_date' => now()->subMonths(11),
            'end_date' => now()->addDays(30),
            'alert_days' => 30,
            'status' => 'active',
            'signature_validated' => true,
        ]);

        // Contrato 2: Vence em 40 dias com alerta de 30 dias (não deve disparar hoje!)
        $contractNotTriggering = Contract::create([
            'company_id' => $company->id,
            'provider_id' => $provider->id,
            'responsible_id' => $responsible->id,
            'contract_number' => 'CTR-NO-TRIGGER',
            'title' => 'Contrato Alerta Futuro',
            'start_date' => now()->subMonths(11),
            'end_date' => now()->addDays(40),
            'alert_days' => 30,
            'status' => 'active',
            'signature_validated' => true,
        ]);

        // Executa o comando Artisan
        $this->artisan('contracts:send-expiry-alerts')
            ->expectsOutput('Disparados 1 alertas de vencimento de contratos.')
            ->assertExitCode(0);

        // Verifica se o email do primeiro contrato foi enviado
        Mail::assertSent(ContractExpiryAlert::class, function (ContractExpiryAlert $mail) use ($contractTriggering, $responsible) {
            return $mail->contract->id === $contractTriggering->id &&
                   $mail->hasTo($responsible->email) &&
                   $mail->envelope()->subject === '[Alerta CLM] O contrato com o cliente Empresa A vence em 30 dias. É necessário renovar?';
        });

        // Verifica se o segundo não foi enviado
        Mail::assertNotSent(ContractExpiryAlert::class, function (ContractExpiryAlert $mail) use ($contractNotTriggering) {
            return $mail->contract->id === $contractNotTriggering->id;
        });
    }
}
