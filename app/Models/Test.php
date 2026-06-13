<?php

namespace App\Models;

use App\Enums\TestType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'max_score',
        'evaluation_criteria',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TestType::class,
            'max_score' => 'float',
        ];
    }

    /**
     * Get the questions for the test.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class)->orderBy('order');
    }

    /**
     * Get the vacancies associated with the test.
     */
    public function vacancies(): BelongsToMany
    {
        return $this->belongsToMany(Vacancy::class, 'vacancy_test')
            ->withPivot('weight')
            ->withTimestamps();
    }

    /**
     * Calculate the score for a multiple choice set of answers.
     *
     * @param  array<int, array<int>>  $answers  question_id => selected option indices
     */
    public function calculateMultipleChoiceScore(array $answers): float
    {
        if ($this->type !== TestType::MULTIPLE_CHOICE) {
            return 0;
        }

        $score = 0.0;

        foreach ($this->questions as $question) {
            $selected = $answers[$question->id] ?? [];
            sort($selected);

            $correct = $question->correct_answer_indices ?? [];
            sort($correct);

            if ($selected === $correct) {
                $score += (float) $question->points;
            }
        }

        return $score;
    }

    /**
     * Apply a manual override to an auto-calculated score.
     */
    public function applyManualOverride(float $calculatedScore, float $manualScore, bool $isManualOverride): array
    {
        return [
            'score' => $isManualOverride ? $manualScore : $calculatedScore,
            'is_manual_override' => $isManualOverride,
        ];
    }
}
