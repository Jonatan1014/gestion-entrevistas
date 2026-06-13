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
     * Get the results recorded for the test.
     */
    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }
}
