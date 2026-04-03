<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->comment('カテゴリID');
            $table->string('name', 100)->comment('指標名（例：売上合計、開業店舗数）');
            $table->enum('type', ['currency', 'quantity', 'percent'])->comment('指標タイプ（金額、数量、率）');
            $table->string('unit', 20)->nullable()->comment('単位（円、店、食、%など）');
            $table->integer('sort_order')->default(0)->comment('表示順序');
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index('category_id');
            $table->index('type');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};























