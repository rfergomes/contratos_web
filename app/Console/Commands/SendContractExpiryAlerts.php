<?php

namespace App\Console\Commands;

use App\Mail\ContractExpiryAlert;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendContractExpiryAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:send-expiry-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca contratos próximos do vencimento de vigência e dispara e-mail de alerta para o responsável.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Desativa escopos globais se houver (garante a leitura completa em background)
        $contracts = Contract::withoutGlobalScopes()
            ->with(['company', 'responsible', 'provider'])
            ->where('status', 'active')
            ->get();

        $today = now()->toDateString();
        $alertsSent = 0;

        foreach ($contracts as $contract) {
            if (! $contract->end_date) {
                continue;
            }

            // Calcula a data de alerta: Data_Fim - Dias_Alerta
            $alertDate = $contract->end_date->copy()->subDays($contract->alert_days);

            if ($alertDate->toDateString() === $today) {
                $email = null;

                if ($contract->responsible && $contract->responsible->active) {
                    $email = $contract->responsible->email;
                } else {
                    // Fallback: Busca o primeiro gestor ativo associado àquela empresa contratante
                    $fallbackGestor = User::withoutGlobalScopes()
                        ->where('role', 'gestor')
                        ->where('company_id', $contract->company_id)
                        ->where('active', true)
                        ->first();

                    if ($fallbackGestor) {
                        $email = $fallbackGestor->email;
                        $contract->setRelation('responsible', $fallbackGestor);
                    }
                }

                if ($email) {
                    Mail::to($email)->send(new ContractExpiryAlert($contract));
                    $alertsSent++;
                }
            }
        }

        $this->info("Disparados {$alertsSent} alertas de vencimento de contratos.");

        return Command::SUCCESS;
    }
}
