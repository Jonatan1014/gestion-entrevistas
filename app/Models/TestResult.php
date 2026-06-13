<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestResult extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'test_id',
        'applicant_id',
        'vacancy_id',
        'evaluator_id',
        'score',
        'observations',
        'is_manual_override',
        'override_justification',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'is_manual_override' => 'boolean',
        ];
    }

    /**
     * Get the test that owns the result.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the applicant that owns the result.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /**
     * Get the vacancy that owns the result.
     */
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    /**
     * Get the evaluator who recorded the result.
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * Get the answers for the result.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(TestAnswer::class);
    }
}
