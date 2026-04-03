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
        Schema::create('evidence_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('monthly_result_id')->comment('月次実績ID');
            $table->string('detail', 500)->comment('詳細');
            $table->decimal('amount', 15, 2)->comment('金額（千円単位）');
            $table->integer('sort_order')->default(0)->comment('表示順序');
            $table->timestamps();
            
            $table->foreign('monthly_result_id')->references('id')->on('monthly_results')->onDelete('cascade');
            $table->index('monthly_result_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_details');
    }
};
