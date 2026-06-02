<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    /**
     * Trava de recursão para evitar loop infinito ao consultar o modelo User.
     */
    protected static bool $applying = false;

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (self::$applying) {
            return;
        }

        self::$applying = true;

        try {
            if (Auth::check()) {
                $user = Auth::user();

                if ($user->isGestor() || ($user->isSuperAdmin() && $user->company_id)) {
                    // Filtra pelo company_id se a tabela possuir essa coluna
                    if ($model->getTable() === 'contracts' || $model->getTable() === 'users') {
                        $builder->where($model->getTable().'.company_id', $user->company_id);
                    }
                } elseif ($user->isFornecedor()) {
                    // Filtra pelo provider_id se for um fornecedor e apenas externos
                    if ($model->getTable() === 'contracts') {
                        $builder->where($model->getTable().'.provider_id', $user->provider_id)
                            ->where($model->getTable().'.management_type', 'external');
                    }
                }
            }
        } finally {
            self::$applying = false;
        }
    }
}
