<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Timetable extends Model {
    protected $fillable=['course_id','semester_id','section_id','subject_id','teacher_id','day_of_week','start_time','end_time','room'];
    public function course(){ return $this->belongsTo(Course::class); }
    public function semester(){ return $this->belongsTo(Semester::class); }
    public function section(){ return $this->belongsTo(Section::class); }
    public function subject(){ return $this->belongsTo(Subject::class); }
    public function teacher(){ return $this->belongsTo(Teacher::class); }
}
