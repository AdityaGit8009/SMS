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

it('previews and repeats a verified legacy migration without losing the private file', function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->seed(SmsDemoSeeder::class);
    $path = 'student-documents/legacy.txt';
    Student::sole()->documents()->create(['type' => 'other', 'file_path' => $path]);
    Storage::disk('public')->put($path, 'Development attachment');

    $this->artisan('sms:privatize-documents --dry-run')->assertSuccessful();
    Storage::disk('public')->assertExists($path);
    Storage::disk('local')->assertMissing($path);
    $this->artisan('sms:privatize-documents')->assertSuccessful();
    $this->artisan('sms:privatize-documents')->assertSuccessful();

    expect(Storage::disk('local')->get($path))->toBe('Development attachment');
    Storage::disk('public')->assertMissing($path);
});

it('leaves conflicting copies intact for manual inspection', function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->seed(SmsDemoSeeder::class);
    $path = 'student-documents/conflict.txt';
    Student::sole()->documents()->create(['type' => 'other', 'file_path' => $path]);
    Storage::disk('public')->put($path, 'Public version');
    Storage::disk('local')->put($path, 'Private version');

    $this->artisan('sms:privatize-documents')->assertFailed();

    expect(Storage::disk('public')->get($path))->toBe('Public version');
    expect(Storage::disk('local')->get($path))->toBe('Private version');
});
