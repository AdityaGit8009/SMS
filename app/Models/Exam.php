<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Exam extends Model {
    protected $fillable=['name','course_id','semester_id','academic_session_id','start_date','end_date','status'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date']; }
    public function course(){ return $this->belongsTo(Course::class); }
    public function semester(){ return $this->belongsTo(Semester::class); }
    public function academicSession(){ return $this->belongsTo(AcademicSession::class); }
    public function schedules(){ return $this->hasMany(ExamSchedule::class); }
}
