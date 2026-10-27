<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherIncomeCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'other_income_categories';

    protected $fillable = [
        'name',
        'code',
        'requires_supplier',
        'description',
        'ordering',
        'status',
        'is_system',
    ];

    protected $casts = [
        'requires_supplier' => 'boolean',
        'is_system'         => 'boolean',
        'status'            => 'integer',
        'ordering'          => 'integer',
    ];

    public function incomes(): HasMany
    {
        return $this->hasMany(OtherIncome::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
