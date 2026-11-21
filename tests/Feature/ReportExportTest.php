<?php

use App\Models\FeePayment;
use App\Models\Student;
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

it('streams every payment exactly once across a multi-chunk export', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    $rows = [];
    foreach (range(1, 505) as $number) {
        $rows[] = ['student_id' => $student->id, 'amount' => 1, 'payment_date' => '2026-11-01', 'payment_method' => 'cash', 'transaction_id' => 'receipt-'.$number];
    }
    foreach (array_chunk($rows, 100) as $chunk) {
        FeePayment::insert($chunk);
    }

    $file = $this->actingAs(User::where('role', 'admin')->sole())->get(route('admin.reports.fees.csv'))->streamedContent();
    $lines = array_slice(explode("\n", trim($file)), 1);
    $receipts = array_map(fn (string $line) => str_getcsv($line, escape: '')[6], $lines);

    expect($receipts)->toBe(array_map(fn (int $number) => 'receipt-'.$number, range(1, 505)));
});
