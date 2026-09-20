<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudentDocument extends Model {
    protected $fillable=['student_id','type','title','file_path'];
    public function student(){ return $this->belongsTo(Student::class); }
}
