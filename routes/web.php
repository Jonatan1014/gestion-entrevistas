<?php

use App\Enums\VacancyStatus;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    $stats = [
        'activeVacancies' => Vacancy::where('status', VacancyStatus::OPEN)->count(),
        'totalApplicants' => Applicant::where('is_blocked', false)->count(),
        'upcomingInterviews' => Interview::where('scheduled_at', '>=', now())
            ->where('status', 'pending')
            ->count(),
        'completedInterviews' => Interview::where('status', 'completed')->count(),
    ];

    $recentApplicants = Applicant::with('createdBy')
        ->where('is_blocked', false)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get()
        ->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'email' => $a->email,
            'created_at' => $a->created_at->diffForHumans(),
            'created_by' => $a->createdBy?->name,
        ]);

    return Inertia::render('Dashboard', [
        'stats' => $stats,
        'recentApplicants' => $recentApplicants,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/modules/roles.php';
require __DIR__.'/modules/users.php';
require __DIR__.'/modules/vacancies.php';
require __DIR__.'/modules/applicants.php';
require __DIR__.'/modules/tests.php';
require __DIR__.'/modules/test-results.php';
require __DIR__.'/modules/interviews.php';
require __DIR__.'/modules/reports.php';
require __DIR__.'/modules/quick-score.php';
