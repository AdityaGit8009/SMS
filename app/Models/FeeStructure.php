<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FeeStructure extends Model {
    protected $fillable=['course_id','academic_session_id','name','type','amount'];
    protected function casts(): array { return ['amount'=>'decimal:2']; }
    public function course(){ return $this->belongsTo(Course::class); }
    public function academicSession(){ return $this->belongsTo(AcademicSession::class); }
    public function payments(){ return $this->hasMany(FeePayment::class); }
}
