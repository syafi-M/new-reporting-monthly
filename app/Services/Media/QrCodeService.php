<?php

namespace App\Services\Media;

use App\Repositories\Contracts\QrCodeRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class QrCodeService
{
    private const QR_TARGET_BASE_URL = 'https://laporan-sac.sac-po.com/send-img/laporan';
    private const DEFAULT_KEGIATAN_OPTIONS = [
        'Progres glass cleaning',
        'Progres general cleaning',
        'Progres pembasmian gulma',
        'Progres pembersihan toilet',
        'Progres pembersihan taman',
        'Progres pembersihan lantai',
        'Progres pembersihan kaca',
        'Progres pembersihan plafon',
        'Progres pembersihan dinding',
        'Progres pembersihan saluran air',
        'Progres pembuangan sampah',
        'Progres disinfeksi area',
        'Progres perawatan landscape',
        'Progres pengepelan area',
        'Progres penyapuan area',
        'Progres high dusting',
        'Progres fogging',
        'Progres vacuum karpet',
    ];

    public function __construct(
        private readonly QrCodeRepositoryInterface $repository,
        private readonly QrCodeStorageService $storage,
    ) {}

    public function indexData(string $search): array
    {
        return [
            'qrCodes' => $this->repository->paginate($search),
            'search' => $search,
        ];
    }

    public function kegiatanOptions(): array
    {
        $storedOptions = $this->repository
            ->allDataValues()
            ->map(fn (string $storedData) => self::splitStoredData($storedData)['kegiatan'])
            ->filter(fn (string $kegiatan) => $kegiatan !== '');

        return $storedOptions
            ->merge(self::DEFAULT_KEGIATAN_OPTIONS)
            ->map(fn (string $kegiatan) => trim($kegiatan))
            ->filter()
            ->unique(fn (string $kegiatan) => mb_strtolower($kegiatan))
            ->sort()
            ->values()
            ->all();
    }

    public static function splitStoredData(string $storedData): array
    {
        $parts = explode('-', $storedData, 2);

        return [
            'data' => trim($parts[0] ?? ''),
            'kegiatan' => trim($parts[1] ?? ''),
        ];
    }

    public static function combineData(string $data, ?string $kegiatan = null): string
    {
        $data = trim($data);
        $kegiatan = trim((string) $kegiatan);

        return $kegiatan !== '' ? "{$data}-{$kegiatan}" : $data;
    }

    public static function buildTargetUrl(string $data, ?string $kegiatan = null, ?int $qrId = null): string
    {
        if($qrId) {
            $targetUrl = self::QR_TARGET_BASE_URL;
        } else {
            $targetUrl = self::QR_TARGET_BASE_URL . '?n=' . rawurlencode(trim($data));
            $kegiatan = trim((string) $kegiatan);
    
            if ($kegiatan !== '') {
                $targetUrl .= '&keg=' . rawurlencode($kegiatan);
            }
        }

        if ($qrId !== null) {
            $targetUrl .= '?id=' . $qrId;
        }

        return $targetUrl;
    }

    public static function buildTargetUrlFromStoredData(string $storedData): string
    {
        $parts = self::splitStoredData($storedData);

        return self::buildTargetUrl($parts['data'], $parts['kegiatan']);
    }

    public function create(string $data, ?string $kegiatan = null, int $qrId = null)
    {
        $storedData = self::combineData($data, $kegiatan);
        $targetUrl = self::buildTargetUrl($data, $kegiatan, $qrId);
        $filename = 'qr/' . Str::uuid() . '.png';
        $qrImage = QrCodeGenerator::format('png')
            ->size(300)
            ->margin(1)
            ->generate($targetUrl);

        Storage::disk('public')->put($filename, $qrImage);

        return $this->repository->create([
            'qr' => $filename,
            'data' => $storedData,
            'id' => $qrId,
        ]);
    }

    public function update(int $id, string $data, ?string $kegiatan = null)
    {
        $qrCode = $this->repository->findOrFail($id);
        $storedData = self::combineData($data, $kegiatan);
        $targetUrl = self::buildTargetUrl($data, $kegiatan, $qrCode->id);

        $qrImage = QrCodeGenerator::format('png')
            ->size(300)
            ->margin(1)
            ->generate($targetUrl);

        Storage::disk('public')->put($qrCode->qr, $qrImage);

        return $this->repository->update($qrCode, [
            'data' => $storedData,
        ]);
    }

    public function getById(int $id)
    {
        return $this->repository->findOrFail($id);
    }

    public function delete(int $id): void
    {
        $qrCode = $this->repository->findOrFail($id);
        $this->storage->delete($qrCode->qr);
        $this->repository->delete($qrCode);
    }
}
