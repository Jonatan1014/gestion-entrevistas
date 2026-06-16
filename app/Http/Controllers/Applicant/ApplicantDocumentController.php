<?php

namespace App\Http\Controllers\Applicant;

use App\Enums\ApplicantDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Applicant\UploadDocumentRequest;
use App\Models\Applicant;
use App\Models\ApplicantDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantDocumentController extends Controller
{
    /**
     * Display a listing of the applicant's documents.
     */
    public function index(Applicant $applicant): Response
    {
        $applicant->load('documents');

        return Inertia::render('applicants/documents/Index', [
            'applicant' => $applicant,
            'documents' => $applicant->documents,
            'canCreateApplicants' => auth()->user()->can('create-applicants'),
        ]);
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(UploadDocumentRequest $request, Applicant $applicant): RedirectResponse
    {
        $file = $request->file('file');
        $type = ApplicantDocumentType::from($request->validated('type'));
        $uuid = (string) Str::uuid();
        $extension = $file->getClientOriginalExtension();
        $filename = "{$uuid}.{$extension}";
        $path = "applicants/{$applicant->id}/{$type->value}/{$filename}";

        $file->storeAs(
            dirname($path),
            $filename,
            ['disk' => 'local']
        );

        ApplicantDocument::create([
            'applicant_id' => $applicant->id,
            'type' => $type->value,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
        ]);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Documento subido correctamente.');
    }

    /**
     * Download the specified document.
     */
    public function download(Applicant $applicant, ApplicantDocument $document): StreamedResponse
    {
        if ($document->applicant_id !== $applicant->id) {
            abort(404);
        }

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    /**
     * Preview the specified document inline in the browser.
     */
    public function preview(Applicant $applicant, ApplicantDocument $document)
    {
        if ($document->applicant_id !== $applicant->id) {
            abort(404);
        }

        return Storage::disk('local')->response($document->path, $document->original_name, [
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Applicant $applicant, ApplicantDocument $document): RedirectResponse
    {
        if ($document->applicant_id !== $applicant->id) {
            abort(404);
        }

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Documento eliminado correctamente.');
    }
}
