<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'periodicity',
        'required',
    ];

    protected $casts = [
        'required' => 'boolean',
    ];

    /**
     * Relacionamento com os Documentos de Contrato
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ContractDocument::class);
    }
}
