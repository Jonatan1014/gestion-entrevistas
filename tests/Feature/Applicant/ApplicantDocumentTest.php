<?php

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    return $admin;
}

function createEntrevistador(): User
{
    $entrevistador = User::factory()->create();
    $entrevistador->assignRole('Entrevistador');

    return $entrevistador;
}

function seedRoles(): void
{
    (new RoleSeeder)->run();
}

// ============================================================================
// APP-002 — Document Uploads
// ============================================================================

describe('APP-002: Document Uploads', function () {
    test('valid CV PDF is stored and associated with applicant', function () {
        seedRoles();
        Storage::fake('local');
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('cv.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/documents",
            [
                'file' => $file,
                'type' => 'cv',
            ]
        );

        $response->assertRedirect();

        $document = ApplicantDocument::first();
        expect($document)->not->toBeNull();
        expect($document->applicant_id)->toBe($applicant->id);
        expect($document->type)->toBe('cv');
        expect($document->original_name)->toBe('cv.pdf');
        expect($document->mime_type)->toBe('application/pdf');

        Storage::disk('local')->assertExists($document->path);
    });

    test('valid certificate DOCX is stored', function () {
        seedRoles();
        Storage::fake('local');
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('certificate.docx', 512, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/documents",
            [
                'file' => $file,
                'type' => 'certificate',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('applicant_documents', [
            'applicant_id' => $applicant->id,
            'type' => 'certificate',
            'original_name' => 'certificate.docx',
        ]);
    });

    test('oversized file is rejected', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('big.pdf', 6 * 1024, 'application/pdf');

        $response = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/documents",
            [
                'file' => $file,
                'type' => 'cv',
            ]
        );

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseCount('applicant_documents', 0);
    });

    test('invalid file format is rejected', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('virus.exe', 1024, 'application/x-msdownload');

        $response = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/documents",
            [
                'file' => $file,
                'type' => 'cv',
            ]
        );

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseCount('applicant_documents', 0);
    });

    test('document type is required and must be valid', function () {
        seedRoles();
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('cv.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($entrevistador)->post(
            "/applicants/{$applicant->id}/documents",
            [
                'file' => $file,
                'type' => 'invalid',
            ]
        );

        $response->assertSessionHasErrors(['type']);
    });

    test('authorized user can download document', function () {
        seedRoles();
        Storage::fake('local');
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('cv.pdf', 1024, 'application/pdf');
        $this->actingAs($entrevistador)->post("/applicants/{$applicant->id}/documents", [
            'file' => $file,
            'type' => 'cv',
        ]);

        $document = ApplicantDocument::first();

        $response = $this->actingAs($entrevistador)->get(
            "/applicants/{$applicant->id}/documents/{$document->id}/download"
        );

        $response->assertOk();
        $response->assertHeader('content-disposition');
    });

    test('authorized user can delete document', function () {
        seedRoles();
        Storage::fake('local');
        $entrevistador = createEntrevistador();
        $applicant = Applicant::factory()->create();

        $file = UploadedFile::fake()->create('cv.pdf', 1024, 'application/pdf');
        $this->actingAs($entrevistador)->post("/applicants/{$applicant->id}/documents", [
            'file' => $file,
            'type' => 'cv',
        ]);

        $document = ApplicantDocument::first();
        $path = $document->path;

        $response = $this->actingAs($entrevistador)->delete(
            "/applicants/{$applicant->id}/documents/{$document->id}"
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('applicant_documents', ['id' => $document->id]);
        Storage::disk('local')->assertMissing($path);
    });
});
