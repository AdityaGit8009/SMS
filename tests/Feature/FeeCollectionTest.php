<?php

use App\Models\AcademicSession;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('offers fee structures beyond the current management table page', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    foreach (range(1, 14) as $number) {
        FeeStructure::create(['name' => 'Lab '.$number, 'amount' => 10.25, 'course_id' => $student->course_id, 'academic_session_id' => $student->academic_session_id]);
    }

    $this->actingAs(User::where('role', 'admin')->sole())->get(route('admin.fees.index'))
        ->assertViewHas('structures', fn ($structures) => $structures->count() === 12)
        ->assertViewHas('paymentStructures', fn ($items) => $items->count() === 16)
        ->assertSee('name="fee_structure_id" required', false)
        ->assertSeeText('Lab 14 (₹10.25)');
});

it('collects the exact remaining cent balance and rejects another payment', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    $structure = FeeStructure::where('type', 'tuition')->firstOrFail();
    $structure->update(['amount' => '0.30']);
    $payload = ['student_id' => $student->id, 'fee_structure_id' => $structure->id, 'payment_date' => '2026-11-10', 'payment_method' => 'cash'];
    $this->actingAs(User::where('role', 'admin')->sole());

    foreach (['0.20', '0.10'] as $amount) {
        $this->post(route('admin.fees.payments.store'), $payload + ['amount' => $amount])
            ->assertRedirectToRoute('admin.fees.receipt', FeePayment::latest('id')->firstOrFail());
    }
    $this->post(route('admin.fees.payments.store'), $payload + ['amount' => '0.01'])->assertSessionHasErrors('amount');

    $this->assertDatabaseCount('fee_payments', 2);
    expect(round((float) FeePayment::sum('amount'), 2))->toBe(0.30);
});

it('returns a mismatched fee structure to the collection form', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    $year = AcademicSession::create(['name' => 'Archive', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31']);
    $structure = FeeStructure::create(['name' => 'Archive lab', 'course_id' => $student->course_id, 'academic_session_id' => $year->id, 'amount' => 10]);

    $this->actingAs(User::where('role', 'admin')->sole())->post(route('admin.fees.payments.store'), [
        'student_id' => $student->id, 'fee_structure_id' => $structure->id, 'amount' => 1,
        'payment_date' => '2026-11-10', 'payment_method' => 'cash',
    ])->assertSessionHasErrors('fee_structure_id');

    $this->assertDatabaseEmpty('fee_payments');
});

it('carries the other fee table page when navigating either list', function () {
    $user = User::factory()->create(['role' => 'admin', 'status' => true]);

    $this->actingAs($user)->get(route('admin.fees.index', ['structures_page' => 2, 'payments_page' => 3]))
        ->assertViewHas('structures', fn ($rows) => str_contains($rows->url(1), 'payments_page=3'))
        ->assertViewHas('payments', fn ($rows) => str_contains($rows->url(1), 'structures_page=2'));
});

it('totals outstanding balances per student and lists only unpaid balances', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    $overpaid = $student->replicate()->fill(['user_id' => User::factory()->create()->id, 'roll_no' => 'R2', 'admission_no' => 'A2']);
    $overpaid->save();
    foreach ([[$student, 5000], [$overpaid, 80000]] as [$payer, $amount]) {
        FeePayment::create(['student_id' => $payer->id, 'payment_date' => '2026-11-01', 'payment_method' => 'cash', 'amount' => $amount]);
    }

    $this->actingAs(User::where('role', 'admin')->sole())->get(route('admin.dashboard'))
        ->assertViewHas('stats', fn ($stats) => (float) $stats['pending_fees'] === 70000.0);
    $this->get(route('admin.reports.index'))
        ->assertViewHas('pendingRows', fn ($rows) => $rows->modelKeys() === [$student->id] && (float) $rows->first()->fee_pending_total === 70000.0);
});
