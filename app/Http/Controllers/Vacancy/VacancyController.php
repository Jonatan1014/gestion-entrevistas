<?php

namespace App\Http\Controllers\Vacancy;

use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vacancy\ChangeStatusRequest;
use App\Http\Requests\Vacancy\StoreVacancyRequest;
use App\Http\Requests\Vacancy\UpdateVacancyRequest;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VacancyController extends Controller
{
    /**
     * Display a listing of vacancies.
     */
    public function index(): Response
    {
        $vacancies = Vacancy::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('vacancies/Index', [
            'vacancies' => $vacancies,
            'canCreateVacancies' => auth()->user()->can('create-vacancies'),
            'canEditVacancies' => auth()->user()->can('edit-vacancies'),
            'canDeleteVacancies' => auth()->user()->can('delete-vacancies'),
        ]);
    }

    /**

    /**
     * Show the form for creating a new vacancy.
     */
    public function create(): Response
    {
        return Inertia::render('vacancies/Create');
    }

    /**
     * Store a newly created vacancy.
     */
    public function store(StoreVacancyRequest $request): RedirectResponse
    {
        $vacancy = Vacancy::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacancy created successfully.');
    }

    /**
     * Display the specified vacancy.
     */
    public function show(Vacancy $vacancy): Response
    {
        $vacancy->load('creator');

        return Inertia::render('vacancies/Show', [
            'vacancy' => $vacancy,
            'canEditVacancies' => auth()->user()->can('edit-vacancies'),
            'canDeleteVacancies' => auth()->user()->can('delete-vacancies'),
        ]);
    }

    /**
     * Show the form for editing the specified vacancy.
     */
    public function edit(Vacancy $vacancy): Response
    {
        return Inertia::render('vacancies/Edit', [
            'vacancy' => $vacancy,
        ]);
    }

    /**
     * Update the specified vacancy.
     */
    public function update(UpdateVacancyRequest $request, Vacancy $vacancy): RedirectResponse
    {
        $vacancy->update($request->validated());

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacancy updated successfully.');
    }

    /**
     * Remove the specified vacancy (soft delete).
     */
    public function destroy(Vacancy $vacancy): RedirectResponse
    {
        $vacancy->delete();

        return redirect()->route('vacancies.index')
            ->with('success', 'Vacancy deleted successfully.');
    }

    /**
     * Close an open vacancy.
     */
    public function close(Vacancy $vacancy): RedirectResponse
    {
        if ($vacancy->status !== VacancyStatus::OPEN) {
            abort(403, 'Only open vacancies can be closed.');
        }

        $vacancy->update(['status' => VacancyStatus::CLOSED]);

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacancy closed successfully.');
    }

    /**
     * Cancel an open vacancy.
     */
    public function cancel(Vacancy $vacancy): RedirectResponse
    {
        if ($vacancy->status !== VacancyStatus::OPEN) {
            abort(403, 'Only open vacancies can be cancelled.');
        }

        $vacancy->update(['status' => VacancyStatus::CANCELLED]);

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacancy cancelled successfully.');
    }

    /**
     * Reopen a closed vacancy.
     */
    public function reopen(Vacancy $vacancy): RedirectResponse
    {
        if ($vacancy->status !== VacancyStatus::CLOSED) {
            abort(403, 'Only closed vacancies can be reopened.');
        }

        $vacancy->update(['status' => VacancyStatus::OPEN]);

        return redirect()->route('vacancies.show', $vacancy)
            ->with('success', 'Vacancy reopened successfully.');
    }
}