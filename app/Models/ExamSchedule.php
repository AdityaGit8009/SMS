<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExamSchedule extends Model {
    protected $fillable=['exam_id','subject_id','exam_date','start_time','end_time','max_marks','passing_marks'];
    protected function casts(): array { return ['exam_date'=>'date']; }
    public function exam(){ return $this->belongsTo(Exam::class); }
    public function subject(){ return $this->belongsTo(Subject::class); }
    public function marks(){ return $this->hasMany(Mark::class); }
}
