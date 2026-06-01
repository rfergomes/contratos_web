<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'phone',
        'email',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Ao salvar um contato como principal, desmarcar todos os outros do mesmo fornecedor
        static::saving(function ($contact) {
            if ($contact->is_main) {
                static::where('provider_id', $contact->provider_id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_main' => false]);
            }
        });
    }

    /**
     * Relacionamento com o Fornecedor
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
