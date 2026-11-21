<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class StudentDocument extends Model
{
    protected $fillable = ['student_id', 'type', 'title', 'file_path'];

    public function readDisk(): FilesystemAdapter
    {
        $private = Storage::disk('local');

        return $private->exists($this->file_path) ? $private : Storage::disk('public');
    }

    public function deleteStoredCopies(): void
    {
        Storage::disk('local')->delete($this->file_path);
        Storage::disk('public')->delete($this->file_path);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
