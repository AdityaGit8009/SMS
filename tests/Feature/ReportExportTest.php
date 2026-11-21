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
