<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use setasign\Fpdi\Fpdi;
use Imagick;
use RuntimeException;

class FileHelper
{
    protected const TEMP_UPLOAD_ROOT = 'temp_uploads';
    protected const ALLOWED_IMAGE_MIME_TO_EXTENSION = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    protected static function imageManager(): ImageManager
    {
        return new ImageManager(new Driver());
    }

    protected static function tempDisk()
    {
        return Storage::disk('local');
    }

    protected static function shouldConvertToWebp(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        $sizeKB = filesize($path) / 1024;
        [$width, $height] = getimagesize($path);

        return !($sizeKB < 80 || ($width < 400 && $height < 400));
    }

    protected static function convertToWebp(string $fullPath, int $quality): ?string
    {
        if (!self::shouldConvertToWebp($fullPath)) {
            return null;
        }

        try {
            $manager = self::imageManager();
            $image   = $manager->read($fullPath);

            $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $fullPath);

            $image
                ->scaleDown(height: 1280)
                ->encode(new WebpEncoder(quality: $quality))
                ->save($webpPath);

            if (filesize($webpPath) >= filesize($fullPath)) {
                unlink($webpPath);
                return null;
            }

            unlink($fullPath);

            return $webpPath;

        } catch (\Throwable $e) {
            Log::error('WebP conversion failed', [
                'file' => $fullPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
    /**
     * Upload an image dynamically.
     *
     * @param  \Illuminate\Http\UploadedFile|null  $file
     * @param  string  $folder
     * @param  string|null  $oldFile
     * @param  bool  $useOriginalName
     * @return string|null
     */
    public static function uploadImage(
        ?UploadedFile $file,
        string $folder = 'uploads',
        ?string $oldFile = null,
        bool $useOriginalName = false,
        int $quality = 75,
    ) {
        if (!$file) {
            return $oldFile;
        }

        try {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');

            $filename = $useOriginalName
                ? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $ext
                : Str::uuid() . '.' . $ext;


            $path = $file->storeAs($folder, $filename, 'public');
            $fullPath = storage_path("app/public/{$path}");

            $webpPath = self::convertToWebp($fullPath, $quality);
            $finalPath = $webpPath
                ? str_replace(storage_path('app/public/') , '', $webpPath)
                : $path;

            if (!Storage::disk('public')->exists($finalPath)) {
                throw new RuntimeException('File upload final tidak ditemukan setelah proses simpan.');
            }

            self::deletePublicFileIfExists($oldFile, [$finalPath]);

            return $finalPath;

        } catch (\Throwable $e) {
            Log::error('Upload image failed: ' . $e->getMessage());
            return null;
        }
    }

    public static function storeChunkMetadata(
        int $userId,
        string $uploadId,
        string $field,
        string $originalName,
        int $fileSize,
        string $mimeType,
        int $totalChunks
    ): string {
        $basePath = self::tempBasePath($userId, $uploadId);
        File::ensureDirectoryExists(storage_path('app/' . $basePath . '/chunks'));

        self::writeChunkMetadata($basePath, [
            'user_id' => $userId,
            'upload_id' => $uploadId,
            'field' => $field,
            'original_name' => $originalName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'total_chunks' => $totalChunks,
            'created_at' => now()->toIso8601String(),
        ]);

        return $basePath;
    }

    public static function storeChunkPart(int $userId, string $uploadId, int $chunkIndex, string $binary): string
    {
        $basePath = self::tempBasePath($userId, $uploadId);
        File::ensureDirectoryExists(storage_path('app/' . $basePath . '/chunks'));

        $chunkPath = $basePath . '/chunks/' . $chunkIndex . '.part';
        self::tempDisk()->put($chunkPath, $binary);
        self::touchChunkActivity($basePath);

        return $chunkPath;
    }

    public static function finalizeChunkUpload(int $userId, string $uploadId): array
    {
        $basePath = self::tempBasePath($userId, $uploadId);
        $meta = self::getChunkMetadata($userId, $uploadId);
        $extension = self::safeImageExtension($meta['mime_type'] ?? null, $meta['original_name'] ?? null);
        $mergedRelativePath = $basePath . '/merged.' . $extension;
        $mergedAbsolutePath = storage_path('app/' . $mergedRelativePath);
        File::ensureDirectoryExists(dirname($mergedAbsolutePath));
        $handle = @fopen($mergedAbsolutePath, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('Gagal membuat file temporary hasil upload di path: ' . $mergedAbsolutePath);
        }

        try {
            for ($i = 0; $i < (int) $meta['total_chunks']; $i++) {
                $chunkPath = $basePath . '/chunks/' . $i . '.part';

                if (!self::tempDisk()->exists($chunkPath)) {
                    throw new \RuntimeException("Chunk {$i} belum lengkap.");
                }

                fwrite($handle, self::tempDisk()->get($chunkPath));
            }
        } finally {
            fclose($handle);
        }

        // Chunk parts are no longer needed once merged file exists.
        self::deleteTempRelativePath($basePath . '/chunks');

        $finalizedAt = now()->toIso8601String();
        self::tempDisk()->put($basePath . '/finalized.json', json_encode([
            'merged_relative_path' => $mergedRelativePath,
            'finalized_at' => $finalizedAt,
        ], JSON_PRETTY_PRINT));
        self::touchChunkActivity($basePath, [
            'finalized_at' => $finalizedAt,
        ]);

        $token = Crypt::encryptString(json_encode([
            'user_id' => $userId,
            'upload_id' => $uploadId,
            'field' => $meta['field'],
            'path' => $mergedRelativePath,
            'original_name' => $meta['original_name'],
            'mime_type' => $meta['mime_type'],
            'size' => filesize($mergedAbsolutePath),
        ]));

        return [
            'temp_token' => $token,
            'temp_path' => $mergedRelativePath,
            'original_name' => $meta['original_name'],
            'size' => filesize($mergedAbsolutePath),
            'field' => $meta['field'],
        ];
    }

    public static function moveTempUploadToPublic(string $token, string $folder, ?string $oldFile = null, bool $skipConversion = true, int $quality = 80): ?string
    {
        $payload = self::decodeTempUploadToken($token);
        $tempPath = self::resolveTempUploadPath($payload);

        if ($tempPath === null || !self::tempPathExists($tempPath)) {
            throw new \RuntimeException('File temporary upload hasil chunk tidak ditemukan.');
        }

        $extension = self::safeImageExtension($payload['mime_type'] ?? null, $payload['original_name'] ?? $payload['path']);
        $targetName = Str::uuid() . '.' . $extension;
        $targetRelativePath = trim($folder, '/') . '/' . $targetName;
        $publicDisk = Storage::disk('public');
        $tempDisk = self::tempDisk();

        $readStream = self::openTempReadStream($tempPath);
        $writeResult = false;

        if (is_resource($readStream)) {
            try {
                $writeResult = $publicDisk->writeStream($targetRelativePath, $readStream);
            } finally {
                fclose($readStream);
            }
        } else {
            $tempContents = self::readTempFileContents($tempPath);
            if ($tempContents === null) {
                throw new RuntimeException('Gagal membaca file temporary upload.');
            }

            $writeResult = $publicDisk->put($targetRelativePath, $tempContents);
        }

        if ($writeResult === false || !$publicDisk->exists($targetRelativePath)) {
            throw new RuntimeException('Gagal menyimpan file ke storage final.');
        }

        $finalPath = $targetRelativePath;

        if (!$skipConversion) {
            $fullPath = storage_path('app/public/' . $targetRelativePath);
            $webpPath = self::convertToWebp($fullPath, $quality);
            $finalPath = $webpPath
                ? str_replace(storage_path('app/public/'), '', $webpPath)
                : $targetRelativePath;
        }

        if (!$publicDisk->exists($finalPath) || ($publicDisk->size($finalPath) ?? 0) <= 0) {
            throw new RuntimeException('File final tidak valid setelah proses upload.');
        }

        self::deleteTempUploadByUploadId((int) $payload['user_id'], (string) $payload['upload_id']);
        self::deletePublicFileIfExists($oldFile, [$finalPath]);

        return $finalPath;
    }

    public static function deleteTempUpload(string $token): void
    {
        try {
            $payload = self::decodeTempUploadToken($token);
            self::deleteTempUploadByUploadId((int) $payload['user_id'], (string) $payload['upload_id']);
        } catch (\Throwable $e) {
            Log::warning('Failed to delete temp upload', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function deleteTempUploadByUploadId(int $userId, string $uploadId): void
    {
        $basePath = self::tempBasePath($userId, $uploadId);
        self::deleteTempRelativePath($basePath);

        $userRoot = self::TEMP_UPLOAD_ROOT . '/' . $userId;
        $userRootAbsolutePath = storage_path('app/' . $userRoot);
        if (is_dir($userRootAbsolutePath)) {
            $remainingEntries = File::glob($userRootAbsolutePath . '/*') ?: [];
            if (count($remainingEntries) === 0) {
                @File::deleteDirectory($userRootAbsolutePath);
            }
        }
    }

    public static function decodeTempUploadToken(string $token): array
    {
        $payload = json_decode(Crypt::decryptString($token), true);

        if (!is_array($payload) || empty($payload['user_id']) || empty($payload['upload_id']) || empty($payload['path'])) {
            throw new \RuntimeException('Token temporary upload tidak valid.');
        }

        return $payload;
    }

    public static function getChunkMetadata(int $userId, string $uploadId): array
    {
        $metaPath = self::tempBasePath($userId, $uploadId) . '/meta.json';

        if (!self::tempDisk()->exists($metaPath)) {
            throw new \RuntimeException('Metadata upload tidak ditemukan.');
        }

        $meta = json_decode(self::tempDisk()->get($metaPath), true);

        if (!is_array($meta)) {
            throw new \RuntimeException('Metadata upload rusak.');
        }

        return $meta;
    }

    protected static function tempBasePath(int $userId, string $uploadId): string
    {
        return self::TEMP_UPLOAD_ROOT . '/' . $userId . '/' . $uploadId;
    }

    protected static function deleteTempRelativePath(string $relativePath): void
    {
        $normalized = trim(str_replace('\\', '/', $relativePath), '/');
        self::assertTempPath($normalized);
        $absolutePath = storage_path('app/' . $normalized);

        try {
            if (self::tempDisk()->exists($normalized)) {
                if (self::tempDisk()->directoryExists($normalized)) {
                    self::tempDisk()->deleteDirectory($normalized);
                } else {
                    self::tempDisk()->delete($normalized);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to delete temp directory via storage disk', [
                'path' => $normalized,
                'error' => $e->getMessage(),
            ]);
        }

        if (is_dir($absolutePath)) {
            File::deleteDirectory($absolutePath);
        } elseif (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    protected static function assertTempPath(string $path): void
    {
        $normalized = trim(str_replace('\\', '/', $path), '/');
        $root = self::TEMP_UPLOAD_ROOT . '/';
        if ($normalized === self::TEMP_UPLOAD_ROOT || str_starts_with($normalized, $root)) {
            return;
        }

        throw new RuntimeException('Path cleanup temporary upload tidak valid.');
    }

    protected static function deletePublicFileIfExists(?string $path, array $skipPaths = []): void
    {
        if (empty($path) || $path === 'none') {
            return;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        $normalizedSkips = array_map(
            static fn ($skip) => ltrim(str_replace('\\', '/', (string) $skip), '/'),
            $skipPaths
        );

        if (in_array($normalized, $normalizedSkips, true)) {
            return;
        }

        if (Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }

    protected static function resolveTempUploadPath(array $payload): ?string
    {
        $tokenPath = trim(str_replace('\\', '/', (string) ($payload['path'] ?? '')), '/');
        if ($tokenPath !== '') {
            self::assertTempPath($tokenPath);
            if (self::tempPathExists($tokenPath)) {
                return $tokenPath;
            }
        }

        $basePath = self::tempBasePath((int) $payload['user_id'], (string) $payload['upload_id']);
        self::assertTempPath($basePath);

        $finalizedPath = $basePath . '/finalized.json';
        if (self::tempDisk()->exists($finalizedPath)) {
            $finalizedMeta = json_decode(self::tempDisk()->get($finalizedPath), true);
            $mergedRelativePath = trim(str_replace('\\', '/', (string) ($finalizedMeta['merged_relative_path'] ?? '')), '/');

            if ($mergedRelativePath !== '') {
                self::assertTempPath($mergedRelativePath);
                if (self::tempPathExists($mergedRelativePath)) {
                    return $mergedRelativePath;
                }
            }
        }

        $candidateFiles = self::listTempFiles($basePath);
        foreach ($candidateFiles as $candidate) {
            $normalizedCandidate = trim(str_replace('\\', '/', $candidate), '/');
            if (str_starts_with(basename($normalizedCandidate), 'merged.') && self::tempPathExists($normalizedCandidate)) {
                return $normalizedCandidate;
            }
        }

        Log::warning('Temp upload merged file missing', [
            'user_id' => $payload['user_id'] ?? null,
            'upload_id' => $payload['upload_id'] ?? null,
            'token_path' => $tokenPath ?: null,
            'base_path' => $basePath,
            'available_files' => $candidateFiles,
        ]);

        return null;
    }

    protected static function tempPathExists(string $relativePath): bool
    {
        $normalized = trim(str_replace('\\', '/', $relativePath), '/');
        self::assertTempPath($normalized);

        if (self::tempDisk()->exists($normalized)) {
            return true;
        }

        return is_file(storage_path('app/' . $normalized));
    }

    protected static function openTempReadStream(string $relativePath)
    {
        $normalized = trim(str_replace('\\', '/', $relativePath), '/');
        self::assertTempPath($normalized);

        $stream = self::tempDisk()->readStream($normalized);
        if (is_resource($stream)) {
            return $stream;
        }

        $absolutePath = storage_path('app/' . $normalized);
        if (is_file($absolutePath)) {
            return @fopen($absolutePath, 'rb');
        }

        return false;
    }

    protected static function readTempFileContents(string $relativePath): ?string
    {
        $normalized = trim(str_replace('\\', '/', $relativePath), '/');
        self::assertTempPath($normalized);

        try {
            $contents = self::tempDisk()->get($normalized);
            if (is_string($contents) && $contents !== '') {
                return $contents;
            }
        } catch (\Throwable $e) {
            // Fallback to native filesystem below.
        }

        $absolutePath = storage_path('app/' . $normalized);
        if (!is_file($absolutePath)) {
            return null;
        }

        $contents = @file_get_contents($absolutePath);

        return is_string($contents) && $contents !== '' ? $contents : null;
    }

    protected static function listTempFiles(string $basePath): array
    {
        $normalizedBasePath = trim(str_replace('\\', '/', $basePath), '/');
        self::assertTempPath($normalizedBasePath);

        $files = self::tempDisk()->files($normalizedBasePath);
        $absoluteBasePath = storage_path('app/' . $normalizedBasePath);

        if (is_dir($absoluteBasePath)) {
            foreach (File::files($absoluteBasePath) as $file) {
                $relative = $normalizedBasePath . '/' . $file->getFilename();
                if (!in_array($relative, $files, true)) {
                    $files[] = $relative;
                }
            }
        }

        return $files;
    }

    public static function cleanupOrphanTempUploads(int $ttlMinutes = 30): array
    {
        $ttlMinutes = max(1, $ttlMinutes);
        $nowTs = now()->getTimestamp();
        $deletedDirs = 0;
        $freedBytes = 0;

        if (!self::tempDisk()->exists(self::TEMP_UPLOAD_ROOT)) {
            return [
                'deleted_dirs' => 0,
                'freed_bytes' => 0,
            ];
        }

        $candidateDirs = self::tempDisk()->allDirectories(self::TEMP_UPLOAD_ROOT);

        foreach ($candidateDirs as $dir) {
            if (!preg_match('#^' . preg_quote(self::TEMP_UPLOAD_ROOT, '#') . '/\d+/[^/]+$#', $dir)) {
                continue;
            }

            $dirSize = self::tempDirSize($dir);
            $lastActivityTs = self::tempDirLastActivity($dir);
            $ageMinutes = (int) floor(($nowTs - $lastActivityTs) / 60);

            if ($ageMinutes >= $ttlMinutes) {
                self::tempDisk()->deleteDirectory($dir);
                $deletedDirs++;
                $freedBytes += $dirSize;
            }
        }

        return [
            'deleted_dirs' => $deletedDirs,
            'freed_bytes' => $freedBytes,
        ];
    }

    protected static function tempDirLastActivity(string $dir): int
    {
        $metaPath = $dir . '/meta.json';
        $candidateTimestamps = [];

        if (self::tempDisk()->exists($metaPath)) {
            $meta = json_decode(self::tempDisk()->get($metaPath), true);
            if (is_array($meta)) {
                foreach (['last_activity_at', 'finalized_at', 'created_at'] as $key) {
                    $timestamp = self::parseIsoTimestamp($meta[$key] ?? null);
                    if ($timestamp !== null) {
                        $candidateTimestamps[] = $timestamp;
                    }
                }
            }
        }

        $finalizedPath = $dir . '/finalized.json';
        if (self::tempDisk()->exists($finalizedPath)) {
            $finalizedMeta = json_decode(self::tempDisk()->get($finalizedPath), true);
            if (is_array($finalizedMeta)) {
                $timestamp = self::parseIsoTimestamp($finalizedMeta['finalized_at'] ?? null);
                if ($timestamp !== null) {
                    $candidateTimestamps[] = $timestamp;
                }
            }
        }

        $files = self::tempDisk()->allFiles($dir);
        if (empty($files)) {
            try {
                $candidateTimestamps[] = self::tempDisk()->lastModified($dir);
            } catch (\Throwable $e) {
                $candidateTimestamps[] = now()->getTimestamp();
            }
        }

        foreach ($files as $file) {
            try {
                $candidateTimestamps[] = self::tempDisk()->lastModified($file);
            } catch (\Throwable $e) {
                // Ignore unreadable file timestamps; cleanup should stay robust.
            }
        }

        $lastModified = max($candidateTimestamps ?: [0]);

        return $lastModified > 0 ? $lastModified : now()->getTimestamp();
    }

    protected static function tempDirSize(string $dir): int
    {
        $bytes = 0;
        foreach (self::tempDisk()->allFiles($dir) as $file) {
            try {
                $bytes += self::tempDisk()->size($file);
            } catch (\Throwable $e) {
                // Ignore unreadable file size; cleanup should stay robust.
            }
        }

        return $bytes;
    }

    protected static function writeChunkMetadata(string $basePath, array $attributes): void
    {
        $metaPath = $basePath . '/meta.json';
        $existing = [];

        if (self::tempDisk()->exists($metaPath)) {
            $decoded = json_decode(self::tempDisk()->get($metaPath), true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $payload = array_merge($existing, $attributes, [
            'last_activity_at' => now()->toIso8601String(),
        ]);

        self::tempDisk()->put($metaPath, json_encode($payload, JSON_PRETTY_PRINT));
    }

    protected static function touchChunkActivity(string $basePath, array $attributes = []): void
    {
        self::writeChunkMetadata($basePath, $attributes);
    }

    protected static function parseIsoTimestamp(mixed $value): ?int
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->getTimestamp();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected static function safeImageExtension(?string $mimeType, ?string $fallbackName = null): string
    {
        $normalizedMime = strtolower((string) $mimeType);
        if (isset(self::ALLOWED_IMAGE_MIME_TO_EXTENSION[$normalizedMime])) {
            return self::ALLOWED_IMAGE_MIME_TO_EXTENSION[$normalizedMime];
        }

        $fallbackExt = strtolower((string) pathinfo((string) $fallbackName, PATHINFO_EXTENSION));
        if (in_array($fallbackExt, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $fallbackExt === 'jpeg' ? 'jpg' : $fallbackExt;
        }

        return 'jpg';
    }

    public static function finalizeChunkUploadWithMimeMap(int $userId, string $uploadId, array $allowedMimeToExtension): array
    {
        $basePath = self::tempBasePath($userId, $uploadId);
        $meta = self::getChunkMetadata($userId, $uploadId);
        $extension = self::safeExtensionByMimeMap($meta['mime_type'] ?? null, $meta['original_name'] ?? null, $allowedMimeToExtension);
        $mergedRelativePath = $basePath . '/merged.' . $extension;
        $mergedAbsolutePath = storage_path('app/' . $mergedRelativePath);
        File::ensureDirectoryExists(dirname($mergedAbsolutePath));
        $handle = @fopen($mergedAbsolutePath, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Gagal membuat file temporary hasil upload di path: ' . $mergedAbsolutePath);
        }

        try {
            for ($i = 0; $i < (int) $meta['total_chunks']; $i++) {
                $chunkPath = $basePath . '/chunks/' . $i . '.part';

                if (!self::tempDisk()->exists($chunkPath)) {
                    throw new RuntimeException("Chunk {$i} belum lengkap.");
                }

                fwrite($handle, self::tempDisk()->get($chunkPath));
            }
        } finally {
            fclose($handle);
        }

        self::deleteTempRelativePath($basePath . '/chunks');

        $finalizedAt = now()->toIso8601String();
        self::tempDisk()->put($basePath . '/finalized.json', json_encode([
            'merged_relative_path' => $mergedRelativePath,
            'finalized_at' => $finalizedAt,
        ], JSON_PRETTY_PRINT));
        self::touchChunkActivity($basePath, [
            'finalized_at' => $finalizedAt,
        ]);

        $token = Crypt::encryptString(json_encode([
            'user_id' => $userId,
            'upload_id' => $uploadId,
            'field' => $meta['field'],
            'path' => $mergedRelativePath,
            'original_name' => $meta['original_name'],
            'mime_type' => $meta['mime_type'],
            'size' => filesize($mergedAbsolutePath),
        ]));

        return [
            'temp_token' => $token,
            'temp_path' => $mergedRelativePath,
            'original_name' => $meta['original_name'],
            'size' => filesize($mergedAbsolutePath),
            'field' => $meta['field'],
        ];
    }

    public static function moveTempUploadToPublicWithMimeMap(string $token, string $folder, array $allowedMimeToExtension, ?string $oldFile = null): ?string
    {
        $payload = self::decodeTempUploadToken($token);
        $tempPath = self::resolveTempUploadPath($payload);

        if ($tempPath === null || !self::tempPathExists($tempPath)) {
            throw new RuntimeException('File temporary upload hasil chunk tidak ditemukan.');
        }

        $extension = self::safeExtensionByMimeMap($payload['mime_type'] ?? null, $payload['original_name'] ?? $payload['path'], $allowedMimeToExtension);
        $targetName = Str::uuid() . '.' . $extension;
        $targetRelativePath = trim($folder, '/') . '/' . $targetName;
        $publicDisk = Storage::disk('public');

        $readStream = self::openTempReadStream($tempPath);
        $writeResult = false;

        if (is_resource($readStream)) {
            try {
                $writeResult = $publicDisk->writeStream($targetRelativePath, $readStream);
            } finally {
                fclose($readStream);
            }
        } else {
            $tempContents = self::readTempFileContents($tempPath);
            if ($tempContents === null) {
                throw new RuntimeException('Gagal membaca file temporary upload.');
            }

            $writeResult = $publicDisk->put($targetRelativePath, $tempContents);
        }

        if ($writeResult === false || !$publicDisk->exists($targetRelativePath)) {
            throw new RuntimeException('Gagal menyimpan file ke storage final.');
        }

        if (!$publicDisk->exists($targetRelativePath) || ($publicDisk->size($targetRelativePath) ?? 0) <= 0) {
            throw new RuntimeException('File final tidak valid setelah proses upload.');
        }

        self::deleteTempUploadByUploadId((int) $payload['user_id'], (string) $payload['upload_id']);
        self::deletePublicFileIfExists($oldFile, [$targetRelativePath]);

        return $targetRelativePath;
    }

    protected static function safeExtensionByMimeMap(?string $mimeType, ?string $fallbackName, array $allowedMimeToExtension): string
    {
        $normalizedMime = strtolower((string) $mimeType);
        $normalizedMap = [];
        foreach ($allowedMimeToExtension as $mime => $extension) {
            $normalizedMap[strtolower((string) $mime)] = strtolower((string) $extension);
        }

        if (isset($normalizedMap[$normalizedMime])) {
            return $normalizedMap[$normalizedMime];
        }

        $fallbackExt = strtolower((string) pathinfo((string) $fallbackName, PATHINFO_EXTENSION));
        if (in_array($fallbackExt, array_values($normalizedMap), true)) {
            return $fallbackExt;
        }

        return 'bin';
    }



    /**
     * Delete an image from storage
     *
     * @param string $path
     * @return bool
     */
    public static function deleteImage($path)
    {
        if (empty($path)) {
            return false;
        }

        try {
            // Check if file exists in storage
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }

            // If not in storage, check if it's a direct path
            $fullPath = public_path($path);
            if (File::exists($fullPath)) {
                return File::delete($fullPath);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to delete image: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the full URL for an image path
     *
     * @param string $path
     * @return string
     */
    public static function getImageUrl($path)
    {
        if (empty($path)) {
            return asset('img/placeholder.png'); // Return a placeholder image
        }

        // If path already includes /storage/, return as is
        if (str_contains($path, '/storage/')) {
            return asset($path);
        }

        // If path is from storage, generate URL
        return asset('storage/' . $path);
    }

    /**
     * Delete multiple images
     *
     * @param array $paths
     * @return array
     */
    public static function deleteMultipleImages($paths)
    {
        $results = [];

        foreach ($paths as $path) {
            $results[$path] = self::deleteImage($path);
        }

        return $results;
    }


    public static function mergePdfs($files, $mergedFilePath)
    {
        $pdf = new FPDI();

        foreach ($files as $file) {
            $pageCount = $pdf->setSourceFile($file);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }

        return $pdf->Output($mergedFilePath, 'F');
    }

    public static function lighten_color($hex, $percent)
    {
        $hex = str_replace('#', '', $hex);

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = min(255, $r + 255 * ($percent / 100));
        $g = min(255, $g + 255 * ($percent / 100));
        $b = min(255, $b + 255 * ($percent / 100));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

}
