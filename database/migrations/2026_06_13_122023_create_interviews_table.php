<?php

use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained('vacancies')->restrictOnDelete();
            $table->foreignId('applicant_id')->constrained('applicants')->restrictOnDelete();
            $table->foreignId('interviewer_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('type')->default(InterviewType::VIRTUAL->value);
            $table->string('link')->nullable();
            $table->text('location_notes')->nullable();
            $table->string('status')->default(InterviewStatus::PENDING->value);
            $table->text('cancellation_reason')->nullable();
            $table->text('observations')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['interviewer_id', 'scheduled_at']);
            $table->index(['vacancy_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
