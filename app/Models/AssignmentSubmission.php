<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AssignmentSubmission extends Model {
    protected $fillable=['assignment_id','student_id','file_path','submitted_at','marks','status','remarks'];
    protected function casts(): array { return ['submitted_at'=>'datetime','marks'=>'decimal:2']; }
    public function assignment(){ return $this->belongsTo(Assignment::class); }
    public function student(){ return $this->belongsTo(Student::class); }
}
