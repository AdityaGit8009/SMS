<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Section extends Model {
    protected $fillable=['course_id','name','code'];
    public function course(){ return $this->belongsTo(Course::class); }
    public function students(){ return $this->hasMany(Student::class); }
}
