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
        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')->constrained('test_results')->cascadeOnDelete();
            $table->foreignId('test_question_id')->constrained('test_questions')->restrictOnDelete();
            $table->json('selected_indices')->nullable();
            $table->boolean('is_correct');
            $table->timestamps();

            $table->index('test_result_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_answers');
    }
};
