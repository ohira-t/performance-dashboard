<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class MonthlyResult extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Activity Log設定
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['value', 'comment'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => $this->getLogDescription($eventName));
    }

    /**
     * ログの説明文を生成
     */
    protected function getLogDescription(string $eventName): string
    {
        $metric = $this->metric;
        $metricName = $metric ? $metric->name : '不明な指標';
        $month = $this->target_month ? $this->target_month->format('Y年n月') : '不明';
        
        $actions = [
            'created' => '追加',
            'updated' => '更新',
            'deleted' => '削除',
        ];
        $action = $actions[$eventName] ?? $eventName;
        
        return "{$metricName}（{$month}）を{$action}";
    }

    protected $fillable = [
        'fiscal_year_id',
        'metric_id',
        'target_month',
        'value',
        'evidence_file_path',
        'comment',
    ];

    protected $casts = [
        'target_month' => 'date',
        'value' => 'decimal:2',
    ];

    /**
     * SQLite互換のため、date型は必ずYYYY-MM-DDで保存する。
     *
     * MySQLではdate型に自動で丸められるが、SQLiteだと "YYYY-MM-DD 00:00:00"
     * がそのまま保存され、ユニーク制約と updateOrCreate の検索が噛み合わず
     * 重複挿入で落ちることがあるため。
     */
    public function setTargetMonthAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['target_month'] = null;
            return;
        }

        $this->attributes['target_month'] = Carbon::parse($value)->toDateString();
    }

    /**
     * 年度とのリレーション
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * 指標とのリレーション
     */
    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class);
    }

    /**
     * 根拠実績詳細とのリレーション
     */
    public function evidenceDetails(): HasMany
    {
        return $this->hasMany(EvidenceDetail::class)->orderBy('sort_order');
    }

    /**
     * 根拠資料のURLを取得（さくらインターネット対応）
     */
    public function getEvidenceUrlAttribute(): ?string
    {
        if (!$this->evidence_file_path) {
            return null;
        }

        // さくらインターネット対応: コントローラー経由でアクセス
        $path = str_replace('public/', '', $this->evidence_file_path);
        return route('storage.file', ['path' => $path]);
    }
}

