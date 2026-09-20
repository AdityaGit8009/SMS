<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FeePayment extends Model {
    protected $fillable=['student_id','fee_structure_id','amount','payment_date','payment_method','transaction_id','remarks','received_by'];
    protected function casts(): array { return ['payment_date'=>'date','amount'=>'decimal:2']; }
    public function student(){ return $this->belongsTo(Student::class); }
    public function feeStructure(){ return $this->belongsTo(FeeStructure::class); }
    public function receiver(){ return $this->belongsTo(User::class,'received_by'); }
}
