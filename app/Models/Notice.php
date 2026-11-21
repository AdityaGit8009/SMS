<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = ['created_by', 'title', 'content', 'type', 'target_role', 'course_id', 'publish_date', 'status'];

    protected function casts(): array
    {
        return ['publish_date' => 'date', 'status' => 'boolean'];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', true)->whereDate('publish_date', '<=', today());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
