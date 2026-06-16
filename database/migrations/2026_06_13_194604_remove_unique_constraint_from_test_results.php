<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the unique constraint so applicants can retake tests.
     */
    public function up(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            // Drop FK so we can drop the unique index it depends on
            $table->dropForeign(['test_id']);
            // Now drop the composite unique
            $table->dropUnique('test_results_test_id_applicant_id_vacancy_id_unique');
            // Recreate FK with its own standalone index
            $table->foreign('test_id')->references('id')->on('tests')->restrictOnDelete();
            // Add lookup index for queries
            $table->index(['test_id', 'applicant_id', 'vacancy_id'], 'test_results_lookup');
        });
    }

    /**
     * Restore the unique constraint.
     */
    public function down(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropIndex('test_results_lookup');
            $table->dropForeign(['test_id']);
            $table->unique(['test_id', 'applicant_id', 'vacancy_id']);
            $table->foreign('test_id')->references('id')->on('tests')->restrictOnDelete();
        });
    }
};
