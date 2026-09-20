<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Student extends Model {
    protected $fillable=['user_id','admission_no','roll_no','course_id','semester_id','section_id','academic_session_id','dob','gender','phone','address','photo','status'];
    protected function casts(): array { return ['dob'=>'date','status'=>'boolean']; }
    public function user(){ return $this->belongsTo(User::class); }
    public function course(){ return $this->belongsTo(Course::class); }
    public function semester(){ return $this->belongsTo(Semester::class); }
    public function section(){ return $this->belongsTo(Section::class); }
    public function academicSession(){ return $this->belongsTo(AcademicSession::class); }
    public function documents(){ return $this->hasMany(StudentDocument::class); }
    public function attendances(){ return $this->hasMany(Attendance::class); }
    public function feePayments(){ return $this->hasMany(FeePayment::class); }
    public function marks(){ return $this->hasMany(Mark::class); }
}
