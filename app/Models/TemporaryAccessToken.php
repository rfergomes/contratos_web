<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class TemporaryAccessToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'tokenable_type',
        'tokenable_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the parent tokenable model (ContractRequest or ContractDocument).
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Helper to generate a token for any Morphable Model.
     */
    public static function generateFor(Model $model, int $expiresInDays = 3): self
    {
        return self::create([
            'token' => Str::random(40),
            'tokenable_type' => get_class($model),
            'tokenable_id' => $model->id,
            'expires_at' => now()->addDays($expiresInDays),
        ]);
    }
}
