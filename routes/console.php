<?php

use App\Models\StudentDocument;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sms:privatize-documents {--dry-run}', function (): int {
    $private = Storage::disk('local');
    $public = Storage::disk('public');
    $pending = 0;
    $moved = 0;
    $failed = 0;

    foreach (StudentDocument::query()->select('id', 'file_path')->lazyById(100) as $document) {
        $path = $document->file_path;
        if (! str_starts_with($path, 'student-documents/') || str_contains($path, '..')) {
            $failed++;

            continue;
        }
        if (! $public->exists($path)) {
            if (! $private->exists($path)) {
                $failed++;
            }

            continue;
        }

        $pending++;
        if ($this->option('dry-run')) {
            continue;
        }

        $contents = $public->get($path);
        if (! is_string($contents) || (! $private->exists($path) && ! $private->put($path, $contents))) {
            $failed++;

            continue;
        }
        $checksum = $private->checksum($path, ['checksum_algo' => 'sha256']);
        if (! is_string($checksum) || ! hash_equals(hash('sha256', $contents), $checksum) || ! $public->delete($path)) {
            $failed++;

            continue;
        }
        $moved++;
    }

    $this->info("Public files found: {$pending}. Moved: {$moved}. Missing or failed: {$failed}.");

    return $failed === 0 ? 0 : 1;
})->purpose('Move registered student documents to private storage after verifying their contents');
