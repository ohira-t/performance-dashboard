<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * 月次実績とのリレーション
     */
    public function monthlyResults(): HasMany
    {
        return $this->hasMany(MonthlyResult::class);
    }

    /**
     * アクティブな年度を取得
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->first();
    }
}























