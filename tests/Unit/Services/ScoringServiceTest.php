<?php

use App\Enums\TestType;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Services\ScoringService;
use Illuminate\Database\Eloquent\Collection;

// ============================================================================
// ScoringService — pure calculation unit tests
// ============================================================================

describe('ScoringService::weightedAverage', function () {
    test('calculates weighted average with two tests', function () {
        $results = [
            ['score' => 80.0, 'max_score' => 100.0],
            ['score' => 90.0, 'max_score' => 100.0],
        ];
        $weights = [60.0, 40.0];

        $result = ScoringService::weightedAverage($results, $weights);

        expect($result['score'])->toBe(84.0)
            ->and($result['meets_min_grade'])->toBeFalse()
            ->and($result['breakdown'])->toHaveCount(2);
    });

    test('calculates weighted average with a single test', function () {
        $results = [
            ['score' => 75.0, 'max_score' => 100.0],
        ];
        $weights = [100.0];

        $result = ScoringService::weightedAverage($results, $weights);

        expect($result['score'])->toBe(75.0);
    });

    test('calculates weighted average with all zero scores', function () {
        $results = [
            ['score' => 0.0, 'max_score' => 100.0],
            ['score' => 0.0, 'max_score' => 100.0],
        ];
        $weights = [60.0, 40.0];

        $result = ScoringService::weightedAverage($results, $weights);

        expect($result['score'])->toBe(0.0)
            ->and($result['meets_min_grade'])->toBeFalse();
    });

    test('reports meets_min_grade when threshold is reached', function () {
        $results = [
            ['score' => 80.0, 'max_score' => 100.0],
        ];
        $weights = [100.0];

        $result = ScoringService::weightedAverage($results, $weights, minGrade: 70.0);

        expect($result['meets_min_grade'])->toBeTrue();
    });
});

describe('ScoringService::meetsMinGrade', function () {
    test('returns true when average meets or exceeds minimum grade', function () {
        expect(ScoringService::meetsMinGrade(84.0, 70.0))->toBeTrue();
    });

    test('returns false when average is below minimum grade', function () {
        expect(ScoringService::meetsMinGrade(65.0, 70.0))->toBeFalse();
    });
});

describe('ScoringService::weightSumGuard', function () {
    test('rejects weights that would exceed 100%', function () {
        expect(ScoringService::weightSumGuard([80.0, 30.0], 10.0))->toBeFalse();
    });

    test('accepts weights that stay within 100%', function () {
        expect(ScoringService::weightSumGuard([50.0, 30.0], 20.0))->toBeTrue();
    });
});

describe('ScoringService::calculateMultipleChoiceScore', function () {
    test('calculates score from correct answers', function () {
        $test = new Test([
            'type' => TestType::MULTIPLE_CHOICE,
            'max_score' => 50,
        ]);

        $questions = new Collection(array_map(function ($i) {
            $question = new TestQuestion([
                'points' => 10,
                'correct_answer_indices' => [1],
            ]);
            $question->id = $i;

            return $question;
        }, range(1, 5)));
        $test->setRelation('questions', $questions);

        $selectedAnswers = [
            1 => [1],
            2 => [1],
            3 => [1],
            4 => [1],
            5 => [0],
        ];

        $result = ScoringService::calculateMultipleChoiceScore($test, $selectedAnswers);

        expect($result['score'])->toBe(40.0)
            ->and($result['max_score'])->toBe(50.0)
            ->and($result['correct_count'])->toBe(4)
            ->and($result['total_questions'])->toBe(5);
    });

    test('returns zero for non-multiple-choice tests', function () {
        $test = new Test([
            'type' => TestType::NUMERIC,
            'max_score' => 100,
        ]);

        $result = ScoringService::calculateMultipleChoiceScore($test, []);

        expect($result['score'])->toBe(0.0);
    });
});

describe('ScoringService::applyManualOverride', function () {
    test('returns manual score and flag when justification provided', function () {
        $result = ScoringService::applyManualOverride(40.0, 45.0, 'partial credit');

        expect($result['score'])->toBe(45.0)
            ->and($result['is_manual_override'])->toBeTrue()
            ->and($result['override_justification'])->toBe('partial credit');
    });

    test('returns auto score and no flag without justification', function () {
        $result = ScoringService::applyManualOverride(40.0, 45.0, null);

        expect($result['score'])->toBe(40.0)
            ->and($result['is_manual_override'])->toBeFalse()
            ->and($result['override_justification'])->toBeNull();
    });
});
