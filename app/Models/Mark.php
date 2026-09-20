<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Mark extends Model {
    protected $fillable=['exam_schedule_id','student_id','theory_marks','practical_marks','total_marks','entered_by'];
    protected function casts(): array { return ['theory_marks'=>'decimal:2','practical_marks'=>'decimal:2','total_marks'=>'decimal:2']; }
    public function examSchedule(){ return $this->belongsTo(ExamSchedule::class); }
    public function student(){ return $this->belongsTo(Student::class); }
}
