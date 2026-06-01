<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ContractHistory extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\DocumentScope);
    }

    protected $fillable = [
        'contract_id',
        'user_id',
        'action',
        'title',
        'description',
    ];

    /**
     * Relacionamento com o Contrato
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relacionamento com o usuário que gerou a ação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper estático para registrar eventos de histórico de forma rápida.
     */
    public static function log(int $contractId, string $action, string $title, ?string $description = null, ?int $userId = null): self
    {
        return self::create([
            'contract_id' => $contractId,
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'title' => $title,
            'description' => $description,
        ]);
    }
}
