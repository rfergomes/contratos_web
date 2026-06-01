<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class DocumentScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isGestor()) {
                $builder->whereHas('contract', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                });
            } elseif ($user->isFornecedor()) {
                $builder->whereHas('contract', function ($query) use ($user) {
                    $query->where('provider_id', $user->provider_id);
                });
            }
        }
    }
}
