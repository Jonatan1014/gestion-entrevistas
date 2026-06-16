<?php

namespace App\Models;

use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'vacancy_id',
        'applicant_id',
        'interviewer_id',
        'scheduled_at',
        'type',
        'link',
        'location_notes',
        'status',
        'cancellation_reason',
        'observations',
        'score',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'integer',
            'type' => InterviewType::class,
            'status' => InterviewStatus::class,
        ];
    }

    /**
     * Get the vacancy associated with the interview.
     */
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    /**
     * Get the applicant associated with the interview.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /**
     * Get the interviewer (user) assigned to the interview.
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
