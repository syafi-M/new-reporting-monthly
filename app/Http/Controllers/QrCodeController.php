<?php

namespace App\Http\Controllers;

use App\Http\Requests\QrCodeStoreRequest;
use App\Models\qrCode;
use App\Services\Media\QrCodeService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QrCodeController extends Controller
{
    public function __construct(
        private readonly QrCodeService $service,
    ) {}

    public function index(Request $request)
    {
        $data = $this->service->indexData(trim((string) $request->get('search')));

        return view('pages.admin.qrcode.index', $data);
    }

    public function create()
    {
        return view('pages.admin.qrcode.create', [
            'kegiatanOptions' => $this->service->kegiatanOptions(),
        ]);
    }

    public function edit($id)
    {
        try {
            $qrCode = $this->service->getById((int) $id);

            return view('pages.admin.qrcode.create', [
                'qrCode' => $qrCode,
                'kegiatanOptions' => $this->service->kegiatanOptions(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to open QR edit page.', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('admin-qrcode.index')->withErrors([
                'error' => 'Data QR Code tidak ditemukan.',
            ]);
        }
    }

    public function store(QrCodeStoreRequest $request)
    {
        $qrId = qrCode::max('id') + 1;
        try {
            $validated = $request->validated();
            $this->service->create($validated['data'], $validated['kegiatan'] ?? null, $qrId);

            return redirect()
                ->route('admin-qrcode.index')
                ->with('success', 'QR Code berhasil ditambahkan.');
        } catch (QueryException $e) {
            Log::warning('Failed to create QR code due to DB constraint.', ['error' => $e->getMessage()]);

            return back()->withInput()->withErrors([
                'error' => 'Data QR Code tidak valid. Silakan cek kembali.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Unexpected error while creating QR code.', ['error' => $e->getMessage()]);

            return back()->withInput()->withErrors([
                'error' => 'Terjadi kesalahan saat membuat QR Code. Silakan coba lagi.',
            ]);
        }
    }

    public function update(QrCodeStoreRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $this->service->update((int) $id, $validated['data'], $validated['kegiatan'] ?? null);

            return redirect()
                ->route('admin-qrcode.index')
                ->with('success', 'QR Code berhasil diperbarui.');
        } catch (QueryException $e) {
            Log::warning('Failed to update QR code due to DB constraint.', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'error' => 'Data QR Code tidak valid. Silakan cek kembali.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Unexpected error while updating QR code.', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'error' => 'Terjadi kesalahan saat memperbarui QR Code. Silakan coba lagi.',
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->delete((int) $id);

            return redirect()
                ->route('admin-qrcode.index')
                ->with('success', 'QR Code berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Unexpected error while deleting QR code.', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menghapus QR Code. Silakan coba lagi.',
            ]);
        }
    }
}
