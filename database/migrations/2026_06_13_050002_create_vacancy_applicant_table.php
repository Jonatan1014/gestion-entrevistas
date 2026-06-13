<?php

use App\Enums\VacancyApplicantStatus;
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
        Schema::create('vacancy_applicant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained('vacancies')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->string('status')->default(VacancyApplicantStatus::REGISTERED->value);
            $table->foreignId('final_decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('final_decided_at')->nullable();
            $table->text('justification')->nullable();
            $table->timestamps();

            $table->unique(['vacancy_id', 'applicant_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancy_applicant');
    }
};
