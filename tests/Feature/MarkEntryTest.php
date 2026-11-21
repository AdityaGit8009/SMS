<?php

use App\Models\ExamSchedule;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects an out-of-range class mark without saving earlier rows', function () {
    $this->seed(SmsDemoSeeder::class);
    $first = Student::sole();
    $second = $first->replicate()->fill(['user_id' => User::factory()->create()->id, 'admission_no' => 'A2', 'roll_no' => 'R2']);
    $second->save();

    $this->actingAs(User::where('role', 'admin')->sole())->post(route('admin.marks.store'), [
        'schedule_id' => ExamSchedule::sole()->id,
        'marks' => [$first->id => ['theory' => 50], $second->id => ['theory' => 70, 'practical' => 31]],
    ])->assertSessionHasErrors('marks');

    $this->assertDatabaseEmpty('marks');
});

it('updates existing decimal scores without duplicating exam results', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    $schedule = ExamSchedule::sole();
    $admin = User::where('role', 'admin')->sole();
    $this->actingAs($admin);
    foreach ([10, 22.05] as $theory) {
        $this->post(route('admin.marks.store'), [
            'schedule_id' => $schedule->id, 'marks' => [$student->id => ['theory' => $theory, 'practical' => '0.05']],
        ])->assertSessionHasNoErrors();
    }

    $this->assertDatabaseCount('marks', 1);
    $this->assertDatabaseHas('marks', ['student_id' => $student->id, 'total_marks' => 22.10, 'entered_by' => $admin->id]);
});
