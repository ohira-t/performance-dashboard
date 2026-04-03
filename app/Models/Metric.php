<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metric extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'type',
        'unit',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * カテゴリとのリレーション
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 月次実績とのリレーション
     */
    public function monthlyResults(): HasMany
    {
        return $this->hasMany(MonthlyResult::class);
    }

    /**
     * 特定年度の月次実績を取得
     */
    public function monthlyResultsForFiscalYear(int $fiscalYearId): HasMany
    {
        return $this->hasMany(MonthlyResult::class)->where('fiscal_year_id', $fiscalYearId);
    }

    /**
     * 特定月の実績値を取得
     */
    public function getValueForMonth(int $fiscalYearId, string $targetMonth): ?float
    {
        $result = $this->monthlyResults()
            ->where('fiscal_year_id', $fiscalYearId)
            ->where('target_month', $targetMonth)
            ->first();
        
        return $result?->value;
    }
}























