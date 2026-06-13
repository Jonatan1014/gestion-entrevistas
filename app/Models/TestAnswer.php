<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'test_result_id',
        'test_question_id',
        'selected_indices',
        'is_correct',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'selected_indices' => 'array',
            'is_correct' => 'boolean',
        ];
    }

    /**
     * Get the test result that owns the answer.
     */
    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class);
    }

    /**
     * Get the question that the answer belongs to.
     */
    public function testQuestion(): BelongsTo
    {
        return $this->belongsTo(TestQuestion::class);
    }
}
