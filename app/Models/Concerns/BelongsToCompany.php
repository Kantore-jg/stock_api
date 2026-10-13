<?php

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::creating(function ($model): void {
            if (empty($model->company_id) && Auth::check()) {
                $model->company_id = Auth::user()->company_id;
            }
        });

        static::addGlobalScope('company', function (Builder $builder): void {
            if (Auth::check()) {
                $builder->where(
                    $builder->getModel()->getTable().'.company_id',
                    Auth::user()->company_id
                );
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
