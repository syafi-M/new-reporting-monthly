<?php

namespace App\Services;

use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadImageStorageService
{
    public function resolveImageInput(Request $request, string $field, string $folder, ?string $oldFile = null): ?string
    {
        $tempField = 'temp_' . $field;

        if ($request->filled($tempField)) {
            $tempToken = (string) $request->input($tempField);
            $tempPayload = FileHelper::decodeTempUploadToken($tempToken);
            $resolvedPath = FileHelper::moveTempUploadToPublic($tempToken, $folder, $oldFile, skipConversion: false);

            Log::info('Resolved image from temp upload token', [
                'field' => $field,
                'source' => 'temp_token',
                'conversion_skipped' => false,
                'upload_id' => $tempPayload['upload_id'] ?? null,
                'resolved_path' => $resolvedPath,
                'old_file' => $oldFile,
            ]);

            return $resolvedPath;
        }

        if ($request->hasFile($field)) {
            $resolvedPath = FileHelper::uploadImage($request->file($field), $folder, $oldFile, false);

            Log::info('Resolved image from direct file upload', [
                'field' => $field,
                'source' => 'direct_file',
                'resolved_path' => $resolvedPath,
                'old_file' => $oldFile,
            ]);

            return $resolvedPath;
        }

        return $oldFile;
    }

    public function storeDirectImage(?UploadedFile $file, string $folder, ?string $oldFile = null): ?string
    {
        return FileHelper::uploadImage($file, $folder, $oldFile, false);
    }

    public function initChunkUpload(int $userId, string $field, string $fileName, int $fileSize, string $mimeType, int $totalChunks): string
    {
        $uploadId = (string) \Illuminate\Support\Str::uuid();

        FileHelper::storeChunkMetadata($userId, $uploadId, $field, $fileName, $fileSize, $mimeType, $totalChunks);

        return $uploadId;
    }

    public function storeChunkPart(int $userId, string $uploadId, int $chunkIndex, string $binary): array
    {
        $meta = FileHelper::getChunkMetadata($userId, $uploadId);
        FileHelper::storeChunkPart($userId, $uploadId, $chunkIndex, $binary);

        return $meta;
    }

    public function finalizeChunkUpload(int $userId, string $uploadId): array
    {
        return FileHelper::finalizeChunkUpload($userId, $uploadId);
    }

    public function cancelChunkUpload(int $userId, ?string $tempToken = null, ?string $uploadId = null): void
    {
        if (!empty($tempToken)) {
            FileHelper::deleteTempUpload($tempToken);
            return;
        }

        if (!empty($uploadId)) {
            FileHelper::deleteTempUploadByUploadId($userId, $uploadId);
        }
    }

    public function deleteUploadFiles(object $uploadImage): void
    {
        foreach (['img_before', 'img_proccess', 'img_final'] as $field) {
            $path = $uploadImage->{$field} ?? null;

            if (!empty($path) && $path !== 'none') {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function storePdf(UploadedFile $pdf, string $month, int $clientId): string
    {
        $date = \Carbon\Carbon::parse($month);
        $fileName = $date->format('Y-m') . '-' . $clientId . '.pdf';
        $filePath = 'rekap_foto/' . $fileName;

        Storage::disk('public')->put($filePath, file_get_contents($pdf->getRealPath()));

        return $filePath;
    }

    public function runOpportunisticTempCleanup(int $ttlMinutes = 30): void
    {
        try {
            $lockKey = 'temp_upload_cleanup:last_run';
            if (!Cache::add($lockKey, now()->toIso8601String(), now()->addMinutes(10))) {
                return;
            }

            $result = FileHelper::cleanupOrphanTempUploads($ttlMinutes);

            if (($result['deleted_dirs'] ?? 0) > 0) {
                Log::info('Opportunistic temp cleanup completed', [
                    'deleted_dirs' => $result['deleted_dirs'],
                    'freed_bytes' => $result['freed_bytes'] ?? 0,
                    'ttl_minutes' => $ttlMinutes,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Opportunistic temp cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
