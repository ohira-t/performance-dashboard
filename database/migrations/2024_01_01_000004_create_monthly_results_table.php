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
        Schema::create('monthly_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fiscal_year_id')->comment('年度ID');
            $table->unsignedBigInteger('metric_id')->comment('指標ID');
            $table->date('target_month')->comment('対象月（YYYY-MM-01形式）');
            $table->decimal('value', 15, 2)->nullable()->comment('実績値');
            $table->string('evidence_file_path', 500)->nullable()->comment('根拠資料ファイルパス');
            $table->text('comment')->nullable()->comment('コメント');
            $table->timestamps();
            
            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->onDelete('cascade');
            $table->foreign('metric_id')->references('id')->on('metrics')->onDelete('cascade');
            $table->unique(['fiscal_year_id', 'metric_id', 'target_month'], 'unique_monthly_result');
            $table->index('target_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_results');
    }
};























