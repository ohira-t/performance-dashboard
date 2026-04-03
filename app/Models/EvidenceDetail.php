<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceDetail extends Model
{
    protected $fillable = [
        'monthly_result_id',
        'detail',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * 月次実績とのリレーション
     */
    public function monthlyResult(): BelongsTo
    {
        return $this->belongsTo(MonthlyResult::class);
    }
}
