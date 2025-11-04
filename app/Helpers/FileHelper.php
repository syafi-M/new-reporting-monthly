<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Upload an image dynamically.
     *
     * @param  \Illuminate\Http\UploadedFile|null  $file
     * @param  string  $folder
     * @param  string|null  $oldFile
     * @param  bool  $useOriginalName
     * @return string|null
     */
    public static function uploadImage(?UploadedFile $file, string $folder = 'uploads', ?string $oldFile = null, bool $useOriginalName = false)
    {
        if (!$file) {
            return $oldFile; 
        }

        // delete old file if exists
        if ($oldFile && Storage::disk('public')->exists($oldFile)) {
            Storage::disk('public')->delete($oldFile);
        }

        // dynamic filename
        $filename = $useOriginalName
            ? $file->getClientOriginalName()
            : Str::uuid() . '.' . $file->getClientOriginalExtension();

        // store in public disk
        $path = $file->storeAs($folder, $filename, 'public');

        return $path;
    }
}
