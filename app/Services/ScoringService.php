<?php

namespace App\Services;

use App\Enums\TestType;
use App\Models\Test;

class ScoringService
{
    /**
     * Calculate a weighted average score across test results.
     *
     * Each result must provide `score` and `max_score`. Weights are paired by
     * position with results. Returns the weighted percentage score plus a
     * per-item breakdown.
     *
     * Formula: Σ(result.score / result.max_score * 100 * weight) / Σ weights
     *
     * @param  array<int, array{score: float, max_score: float}>  $results
     * @param  array<int, float>  $weights
     */
    public static function weightedAverage(array $results, array $weights, ?float $minGrade = null): array
    {
        $totalWeight = array_sum($weights);
        $weightedSum = 0.0;
        $breakdown = [];

        foreach ($results as $index => $result) {
            $maxScore = (float) ($result['max_score'] ?? 0);
            $score = (float) ($result['score'] ?? 0);
            $weight = (float) ($weights[$index] ?? 0);

            $normalized = $maxScore > 0 ? ($score / $maxScore) * 100 : 0.0;
            $weightedSum += $normalized * $weight;

            $breakdown[] = [
                'score' => $score,
                'max_score' => $maxScore,
                'weight' => $weight,
                'normalized' => round($normalized, 2),
                'contribution' => round($normalized * $weight, 2),
            ];
        }

        $finalScore = $totalWeight > 0 ? $weightedSum / $totalWeight : 0.0;

        return [
            'score' => round($finalScore, 2),
            'meets_min_grade' => $minGrade !== null ? self::meetsMinGrade($finalScore, $minGrade) : false,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Determine whether a weighted average meets the minimum grade threshold.
     */
    public static function meetsMinGrade(float $weightedAverage, float $minGrade): bool
    {
        return $weightedAverage >= $minGrade;
    }

    /**
     * Guard to ensure adding a new weight keeps the total at or below 100%.
     *
     * @param  array<int, float>  $currentWeights
     */
    public static function weightSumGuard(array $currentWeights, float $newWeight): bool
    {
        return array_sum($currentWeights) + $newWeight <= 100.0;
    }

    /**
     * Calculate the score for a multiple choice test from selected answers.
     *
     * @param  array<int, array<int>>  $selectedAnswers  question_id => selected option indices
     */
    public static function calculateMultipleChoiceScore(Test $test, array $selectedAnswers): array
    {
        if ($test->type !== TestType::MULTIPLE_CHOICE) {
            return [
                'score' => 0.0,
                'max_score' => (float) $test->max_score,
                'correct_count' => 0,
                'total_questions' => 0,
            ];
        }

        $score = 0.0;
        $correctCount = 0;
        $totalQuestions = $test->questions->count();

        foreach ($test->questions as $question) {
            $selected = $selectedAnswers[$question->id] ?? [];
            sort($selected);

            $correct = $question->correct_answer_indices ?? [];
            sort($correct);

            if ($selected === $correct) {
                $score += (float) $question->points;
                $correctCount++;
            }
        }

        return [
            'score' => $score,
            'max_score' => (float) $test->max_score,
            'correct_count' => $correctCount,
            'total_questions' => $totalQuestions,
        ];
    }

    /**
     * Apply a manual override to an auto-calculated score.
     */
    public static function applyManualOverride(float $autoScore, float $manualScore, ?string $justification): array
    {
        $isManualOverride = ! empty($justification);

        return [
            'score' => $isManualOverride ? $manualScore : $autoScore,
            'is_manual_override' => $isManualOverride,
            'override_justification' => $justification,
        ];
    }
}
