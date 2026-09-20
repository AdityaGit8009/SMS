<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Assignment extends Model {
    protected $fillable=['teacher_id','subject_id','title','description','due_date','file_path','max_marks'];
    protected function casts(): array { return ['due_date'=>'datetime']; }
    public function teacher(){ return $this->belongsTo(Teacher::class); }
    public function subject(){ return $this->belongsTo(Subject::class); }
    public function submissions(){ return $this->hasMany(AssignmentSubmission::class); }
}
