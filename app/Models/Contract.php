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
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

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
