<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Department extends Model {
    protected $fillable=['name','code','status'];
    protected function casts(): array { return ['status'=>'boolean']; }
    public function courses(){ return $this->hasMany(Course::class); }
    public function teachers(){ return $this->hasMany(Teacher::class); }
}
