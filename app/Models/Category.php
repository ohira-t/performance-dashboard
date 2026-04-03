<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'sort_order',
    ];

    /**
     * 親カテゴリとのリレーション
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * 子カテゴリとのリレーション
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * 指標とのリレーション
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class)->orderBy('sort_order');
    }

    /**
     * ルートカテゴリ（親がないカテゴリ）を取得
     */
    public static function getRootCategories()
    {
        return self::whereNull('parent_id')->orderBy('sort_order')->get();
    }

    /**
     * 階層構造のフルパスを取得（例：全体 > 企業向け弁当 > 開業店舗数）
     */
    public function getFullPath(): string
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }
}























