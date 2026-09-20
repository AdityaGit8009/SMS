<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model {
    protected $fillable=['course_id','semester_id','teacher_id','name','code','credits'];
    public function course(){ return $this->belongsTo(Course::class); }
    public function semester(){ return $this->belongsTo(Semester::class); }
    public function teacher(){ return $this->belongsTo(Teacher::class); }
    public function attendances(){ return $this->hasMany(Attendance::class); }
}
