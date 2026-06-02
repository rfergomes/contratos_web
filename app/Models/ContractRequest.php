<?php

namespace App\Models;

use App\Models\Scopes\DocumentScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRequest extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new DocumentScope);

        static::created(function ($request) {
            if ($request->contract && ! $request->contract->isInternal()) {
                Alert::createForNewRequest($request);
            }
        });
    }

    protected $fillable = [
        'contract_id',
        'sender_type',
        'user_id',
        'type',
        'title',
        'description',
        'file_path',
        'original_name',
        'status',
        'response_text',
        'response_file_path',
        'response_original_name',
        'due_date',
        'requires_attachment',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'due_date' => 'date',
        'requires_attachment' => 'boolean',
    ];

    /**
     * Relacionamento com o Contrato
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relacionamento com o usuário remetente da solicitação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o usuário que respondeu à solicitação
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
