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
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->restrictOnDelete();
            $table->foreignId('applicant_id')->constrained('applicants')->restrictOnDelete();
            $table->foreignId('vacancy_id')->constrained('vacancies')->restrictOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->restrictOnDelete();
            $table->decimal('score', 5, 2);
            $table->text('observations')->nullable();
            $table->boolean('is_manual_override')->default(false);
            $table->text('override_justification')->nullable();
            $table->timestamps();

            $table->unique(['test_id', 'applicant_id', 'vacancy_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
