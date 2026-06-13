<?php

namespace App\Models;

use App\Enums\VacancyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacancy extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'position',
        'location',
        'requirements',
        'status',
        'min_grade',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => VacancyStatus::class,
            'min_grade' => 'decimal:2',
        ];
    }

    /**
     * Get the user who created the vacancy.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the applicants associated with the vacancy.
     */
    public function applicants(): BelongsToMany
    {
        return $this->belongsToMany(Applicant::class, 'vacancy_applicant')
            ->withPivot('status', 'final_decided_by', 'final_decided_at', 'justification')
            ->withTimestamps();
    }

    /**
     * Scope to get only open vacancies.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', VacancyStatus::OPEN);
    }

    /**
     * Check if the vacancy can transition to a given status.
     */
    public function canTransitionTo(VacancyStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }
}
