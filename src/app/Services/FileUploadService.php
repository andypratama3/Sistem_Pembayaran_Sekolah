<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function store(UploadedFile $file, string $directory, ?string $existingFile = null): string
    {
        if ($existingFile && Storage::disk('public')->exists($existingFile)) {
            Storage::disk('public')->delete($existingFile);
        }
        $filename = Str::random(20).'.'.$file->getClientOriginalExtension();

        return $file->storeAs($directory, $filename, 'public');
    }
}
