<?php

use App\Models\Student;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('uploads a private student document and downloads it as an administrator', function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    $this->actingAs(User::where('role', 'admin')->sole());

    $this->post(route('admin.students.documents.store', $student), [
        'type' => 'other',
        'document' => UploadedFile::fake()->createWithContent('sample.txt', 'Synthetic attachment'),
    ])->assertSessionHasNoErrors();
    $document = $student->documents()->sole();
    Storage::disk('local')->assertExists($document->file_path);
    Storage::disk('public')->assertMissing($document->file_path);
    $this->get(route('admin.students.documents.show', [$student, $document]))->assertDownload(basename($document->file_path));

    $this->actingAs($student->user)->get(route('admin.students.documents.show', [$student, $document]))->assertForbidden();
});

it('keeps legacy documents available and removes their stored file on deletion', function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    $document = $student->documents()->create(['type' => 'other', 'file_path' => 'student-documents/legacy.txt']);
    Storage::disk('public')->put($document->file_path, 'Legacy sample');
    $this->actingAs(User::where('role', 'admin')->sole());

    $this->get(route('admin.students.documents.show', [$student, $document]))->assertDownload('legacy.txt');
    $this->delete(route('admin.students.documents.destroy', [$student, $document]))->assertSessionHasNoErrors();

    $this->assertModelMissing($document);
    Storage::disk('public')->assertMissing($document->file_path);
});
