<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Course extends Model {
    protected $fillable=['department_id','name','code','duration_years','total_semesters','course_fee','status'];
    protected function casts(): array { return ['course_fee'=>'decimal:2','status'=>'boolean']; }
    public function department(){ return $this->belongsTo(Department::class); }
    public function semesters(){ return $this->hasMany(Semester::class); }
    public function sections(){ return $this->hasMany(Section::class); }
    public function subjects(){ return $this->hasMany(Subject::class); }
    public function students(){ return $this->hasMany(Student::class); }
}
