<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractDocument extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\DocumentScope);

        static::saved(function ($document) {
            $contract = $document->contract()->first();
            if ($contract) {
                $contract->updateStatusFromObligationsAndSignature();
            }
        });

        static::deleted(function ($document) {
            $contract = $document->contract()->first();
            if ($contract) {
                $contract->updateStatusFromObligationsAndSignature();
            }
        });
    }

    protected $fillable = [
        'contract_id',
        'document_type_id',
        'file_path',
        'original_name',
        'due_date',
        'status',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'reviewed_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Relacionamento com o Contrato
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relacionamento com a Definição do Tipo do Documento
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Relacionamento com o Gestor que revisou o documento
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
