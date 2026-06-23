<?php

namespace App\Services\Media;

use App\Repositories\Contracts\ImageRateRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageRateService
{
    public function __construct(
        private readonly ImageRateRepositoryInterface $repository,
    ) {}

    public function indexData(array $filters): array
    {
        return $this->repository->paginateWithSummary($filters);
    }

    public function findUploadPreviewByName(?string $rawName)
    {
        if (!$rawName) {
            return null;
        }

        return $this->repository->findUploadByAreaName(strtolower(trim((string) $rawName)));
    }

    public function store(array $payload)
    {
        $imageName = trim((string) ($payload['n'] ?? ''));
        if ($imageName === '') {
            throw new \RuntimeException('Target area rating tidak ditemukan.');
        }

        if (!isset($payload['rate']) || $payload['rate'] === null || $payload['rate'] === '') {
            throw new \RuntimeException('Rating wajib dipilih.');
        }

        $image = $this->repository->findUploadByAreaName(strtolower($imageName));

        if (!$image) {
            throw new \RuntimeException('Image target tidak ditemukan untuk rating.');
        }

        $imagePathRate = null;
        if (isset($payload['image_path_rate']) && $payload['image_path_rate'] instanceof UploadedFile) {
            $imagePathRate = $payload['image_path_rate']->store('image-rates', 'public');
        }

        return $this->repository->create([
            'upload_image_id' => $image->id,
            'image_path_rate' => $imagePathRate,
            'name' => $payload['name'],
            'email' => $payload['email'] ?? null,
            'rate' => (int) $payload['rate'],
            'comment' => $payload['comment'] ?? null,
        ]);
    }

    public function getById(int $id)
    {
        return $this->repository->findOrFail($id);
    }

    public function update(int $id, array $payload)
    {
        $rate = $this->repository->findOrFail($id);

        return $this->repository->update($rate, $payload);
    }

    public function destroy(int $id): void
    {
        $rate = $this->repository->findOrFail($id);
        $this->repository->delete($rate);
    }
}
