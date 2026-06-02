<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'link',
        'type',
        'source_type',
        'source_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Relacionamento com o Usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Escopo para carregar apenas alertas não lidos
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Helper para gerar alertas baseados em prazos para um determinado usuário.
     */
    public static function generateAlertsForUser(User $user): void
    {
        if ($user->isSuperAdmin()) {
            // Super Admin não recebe alertas de prazos de contratos específicos via Dashboard
            return;
        }

        self::generateRequestDeadlineAlerts($user);
        self::generateObligationDeadlineAlerts($user);
    }

    /**
     * Gera lembretes de prazos de solicitações pendentes próximas do vencimento (<= 5 dias).
     */
    private static function generateRequestDeadlineAlerts(User $user): void
    {
        $today = Carbon::today();
        $warningLimit = Carbon::today()->addDays(5);

        // Busca solicitações pendentes com prazo
        $pendingRequests = ContractRequest::where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $warningLimit)
            ->get();

        foreach ($pendingRequests as $request) {
            $contract = $request->contract;
            if (! $contract) {
                continue;
            }

            $shouldAlert = false;

            // Se o remetente foi o fornecedor, o responsável por responder é a empresa (Gestor)
            if ($request->sender_type === 'provider') {
                if ($user->isGestor() && $contract->company_id === $user->company_id) {
                    $shouldAlert = true;
                }
            }
            // Se o remetente foi a empresa, o responsável por responder é o fornecedor
            elseif ($request->sender_type === 'company') {
                if ($user->isFornecedor() && $contract->provider_id && $contract->provider_id === $user->provider_id) {
                    $shouldAlert = true;
                }
            }

            if ($shouldAlert) {
                // Previne duplicados
                $exists = self::where('user_id', $user->id)
                    ->where('source_type', 'ContractRequest')
                    ->where('source_id', $request->id)
                    ->where('type', 'request_deadline')
                    ->whereNull('read_at')
                    ->exists();

                if (! $exists) {
                    $daysRemaining = $today->diffInDays($request->due_date, false);
                    $deadlineMsg = $daysRemaining < 0
                        ? 'atrasada há '.abs($daysRemaining).' dia(s)'
                        : "vence em {$daysRemaining} dia(s)";

                    self::create([
                        'user_id' => $user->id,
                        'title' => 'Prazo de Solicitação Próximo',
                        'message' => "A solicitação '{$request->title}' no contrato {$contract->contract_number} está {$deadlineMsg}.",
                        'link' => route('contracts.show', $contract->id).'#timeline',
                        'type' => 'request_deadline',
                        'source_type' => 'ContractRequest',
                        'source_id' => $request->id,
                    ]);
                }
            }
        }
    }

    /**
     * Gera lembretes de prazos de obrigações de documentos do GED (<= 7 dias).
     */
    private static function generateObligationDeadlineAlerts(User $user): void
    {
        $today = Carbon::today();
        $warningLimit = Carbon::today()->addDays(7);

        // Busca documentos pendentes ou recusados com prazo
        $pendingDocs = ContractDocument::whereIn('status', ['pending', 'rejected'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $warningLimit)
            ->get();

        foreach ($pendingDocs as $doc) {
            $contract = $doc->contract;
            if (! $contract) {
                continue;
            }

            $shouldAlert = false;

            // Se for fornecedor e o contrato é dele
            if ($user->isFornecedor() && $contract->provider_id && $contract->provider_id === $user->provider_id) {
                $shouldAlert = true;
            }
            // Se for gestor e o contrato pertence à empresa dele (para ele cobrar o fornecedor)
            elseif ($user->isGestor() && $contract->company_id === $user->company_id) {
                $shouldAlert = true;
            }

            if ($shouldAlert) {
                $exists = self::where('user_id', $user->id)
                    ->where('source_type', 'ContractDocument')
                    ->where('source_id', $doc->id)
                    ->where('type', 'obligation_deadline')
                    ->whereNull('read_at')
                    ->exists();

                if (! $exists) {
                    $daysRemaining = $today->diffInDays($doc->due_date, false);
                    $deadlineMsg = $daysRemaining < 0
                        ? 'atrasada há '.abs($daysRemaining).' dia(s)'
                        : "vence em {$daysRemaining} dia(s)";

                    $rolePrefix = $user->isFornecedor() ? 'Você precisa enviar' : 'O fornecedor deve enviar';

                    self::create([
                        'user_id' => $user->id,
                        'title' => 'Vencimento de Documento',
                        'message' => "{$rolePrefix} o documento '{$doc->documentType->name}' do contrato {$contract->contract_number} ({$deadlineMsg}).",
                        'link' => route('ged.index'),
                        'type' => 'obligation_deadline',
                        'source_type' => 'ContractDocument',
                        'source_id' => $doc->id,
                    ]);
                }
            }
        }
    }

    /**
     * Cria alerta para nova solicitação aberta.
     */
    public static function createForNewRequest(ContractRequest $request): void
    {
        $contract = $request->contract;
        if (! $contract) {
            return;
        }

        $users = collect();

        // Se aberta por empresa, notifica todos os usuários ativos do fornecedor
        if ($request->sender_type === 'company' && $contract->provider_id) {
            $users = User::withoutGlobalScopes()
                ->where('provider_id', $contract->provider_id)
                ->where('active', true)
                ->get();
        }
        // Se aberta pelo fornecedor, notifica o gestor responsável ou todos os gestores da empresa
        elseif ($request->sender_type === 'provider') {
            if ($contract->responsible_id) {
                $responsible = User::withoutGlobalScopes()->find($contract->responsible_id);
                if ($responsible && $responsible->active) {
                    $users->push($responsible);
                }
            } else {
                $users = User::withoutGlobalScopes()
                    ->where('company_id', $contract->company_id)
                    ->where('role', 'gestor')
                    ->where('active', true)
                    ->get();
            }
        }

        foreach ($users as $user) {
            self::create([
                'user_id' => $user->id,
                'title' => 'Nova Solicitação Recebida',
                'message' => "Uma nova solicitação do tipo '".__($request->type)."' foi aberta no contrato {$contract->contract_number}.",
                'link' => route('contracts.show', $contract->id).'#timeline',
                'type' => 'new_request',
                'source_type' => 'ContractRequest',
                'source_id' => $request->id,
            ]);
        }
    }

    /**
     * Cria alerta informando que uma solicitação foi respondida.
     */
    public static function createForRequestResponse(ContractRequest $request): void
    {
        $contract = $request->contract;
        if (! $contract) {
            return;
        }

        // O destinatário é o usuário que abriu originalmente a solicitação
        $creator = User::withoutGlobalScopes()->find($request->user_id);
        if (! $creator || ! $creator->active) {
            return;
        }

        $statusStr = $request->status === 'resolved' ? 'resolvida' : 'rejeitada';

        self::create([
            'user_id' => $creator->id,
            'title' => 'Solicitação Respondida',
            'message' => "A sua solicitação '{$request->title}' no contrato {$contract->contract_number} foi {$statusStr}.",
            'link' => route('contracts.show', $contract->id).'#timeline',
            'type' => 'request_response',
            'source_type' => 'ContractRequest',
            'source_id' => $request->id,
        ]);
    }
}
