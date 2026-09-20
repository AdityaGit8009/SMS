<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LeaveRequest extends Model {
    protected $fillable=['user_id','from_date','to_date','reason','attachment','status','reviewed_by','reviewed_at','review_note'];
    protected function casts(): array { return ['from_date'=>'date','to_date'=>'date','reviewed_at'=>'datetime']; }
    public function user(){ return $this->belongsTo(User::class); }
    public function reviewer(){ return $this->belongsTo(User::class,'reviewed_by'); }
}
