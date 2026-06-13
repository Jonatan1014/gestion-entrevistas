<?php

namespace App\Enums;

enum VacancyStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    /**
     * Check if the status can transition to a given target status.
     * Valid transitions:
     *   open → closed
     *   open → cancelled
     *   closed → open (reopen)
     *   cancelled → (no transitions allowed)
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::OPEN => $target === self::CLOSED || $target === self::CANCELLED,
            self::CLOSED => $target === self::OPEN,
            self::CANCELLED => false,
        };
    }
}