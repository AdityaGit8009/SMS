<?php

use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('round trips commas, embedded quotes and trailing backslashes in student exports', function () {
    $this->seed(SmsDemoSeeder::class);
    $name = 'Sample, "quoted" ending\\';
    User::where('role', 'student')->sole()->update(['name' => $name]);

    $response = $this->actingAs(User::where('role', 'admin')->sole())->get(route('admin.reports.students.csv'));
    $response->assertDownload('students-report.csv');
    $lines = explode("\n", rtrim($response->streamedContent(), "\r\n"));

    expect(str_getcsv($lines[0], escape: '')[2])->toBe('Name');
    expect(str_getcsv($lines[1], escape: '')[2])->toBe($name);
});

it('exports the filtered roster independently of report pagination', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = User::where('role', 'student')->sole()->student;
    $other = $student->replicate()->fill(['user_id' => User::factory()->create(['name' => 'Other sample'])->id, 'roll_no' => 'OTHER-2', 'admission_no' => 'ADM-2', 'status' => false]);
    $other->save();
    $admin = User::where('role', 'admin')->sole();
    $filters = ['q' => 'OTHER', 'course_id' => $student->course_id, 'status' => '0'];

    $this->actingAs($admin)->get(route('admin.reports.index', $filters))
        ->assertSee(route('admin.reports.students.csv', $filters));
    $export = $this->get(route('admin.reports.students.csv', $filters + ['students' => 2]))->streamedContent();

    expect($export)->toContain('OTHER-2')->not->toContain($student->roll_no);
});
