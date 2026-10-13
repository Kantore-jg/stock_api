<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\CompanySettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['company_id', 'setting_key', 'setting_value'])]
class CompanySetting extends Model
{
    /** @use HasFactory<CompanySettingFactory> */
    use BelongsToCompany, HasFactory;
}
