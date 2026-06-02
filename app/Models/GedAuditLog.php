<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_document_id',
        'user_id',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relacionamento com o Documento de Contrato
     */
    public function contractDocument(): BelongsTo
    {
        return $this->belongsTo(ContractDocument::class, 'contract_document_id');
    }

    /**
     * Relacionamento com o Usuário que realizou a ação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
