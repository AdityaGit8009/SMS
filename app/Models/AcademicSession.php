<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AcademicSession extends Model {
    protected $fillable=['name','start_date','end_date','is_current'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date','is_current'=>'boolean']; }
    public function students(){ return $this->hasMany(Student::class); }
}
