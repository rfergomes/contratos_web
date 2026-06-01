<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\CompanyScope);

        static::saved(function ($contract) {
            $contract->updateStatusFromObligationsAndSignature();
        });
    }

    protected $fillable = [
        'company_id',
        'provider_id',
        'responsible_id',
        'contract_number',
        'title',
        'description',
        'start_date',
        'end_date',
        'alert_days',
        'status',
        'signature_validated',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signature_validated' => 'boolean',
    ];

    /**
     * Recalcula o status do contrato baseado nas obrigações (GED) e na validação de assinatura.
     */
    public function updateStatusFromObligationsAndSignature(): void
    {
        // Se for rascunho ou suspenso manualmente, respeita a decisão
        if (in_array($this->status, ['draft', 'suspended'])) {
            return;
        }

        // Se a data de término já passou, o status torna-se vencido (expired)
        if ($this->end_date && $this->end_date->isPast()) {
            if ($this->status !== 'expired') {
                $this->status = 'expired';
                $this->saveQuietly();
            }
            return;
        }

        // Verifica se há alguma obrigação pendente, submetida ou recusada
        $hasPending = $this->documents()->whereIn('status', ['pending', 'submitted', 'rejected'])->exists();

        // Verifica se a assinatura está confirmada
        $isSigned = (bool) $this->signature_validated;

        // Se houver pendência de documentos OU a assinatura não estiver confirmada, o status é 'pending'
        $newStatus = ($hasPending || !$isSigned) ? 'pending' : 'active';

        if ($this->status !== $newStatus) {
            $oldStatus = $this->status;
            $this->status = $newStatus;
            $this->saveQuietly();

            // Registrar no histórico do contrato
            \App\Models\ContractHistory::log(
                $this->id,
                'status_changed',
                'Status Alterado Automaticamente',
                "O status do contrato foi alterado automaticamente de '" . ($oldStatus ?? 'N/A') . "' para '{$newStatus}' com base no fluxo de obrigações/assinatura."
            );
        }
    }

    /**
     * Relacionamento com o usuário gestor responsável pelo contrato
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    /**
     * Relacionamento com a Empresa Contratante
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relacionamento com o Fornecedor Contratado
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Relacionamento com os Documentos Exigidos/Enviados (GED)
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ContractDocument::class);
    }

    /**
     * Relacionamento com as Solicitações Bi-direcionais
     */
    public function requests(): HasMany
    {
        return $this->hasMany(ContractRequest::class);
    }

    /**
     * Relacionamento com o Histórico / Linha do Tempo
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ContractHistory::class)->orderBy('created_at', 'asc');
    }
}
