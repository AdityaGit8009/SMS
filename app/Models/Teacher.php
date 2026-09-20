<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model {
    protected $fillable=['user_id','employee_id','department_id','qualification','designation','joining_date','phone','address','photo'];
    protected function casts(): array { return ['joining_date'=>'date']; }
    public function user(){ return $this->belongsTo(User::class); }
    public function department(){ return $this->belongsTo(Department::class); }
    public function subjects(){ return $this->hasMany(Subject::class); }
    public function assignments(){ return $this->hasMany(Assignment::class); }
}
