<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageRateStoreRequest;
use App\Http\Requests\ImageRateUpdateRequest;
use App\Models\ImageRate;
use App\Models\qrCode;
use App\Models\User;
use App\Services\Media\ImageRateService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImageRateController extends Controller
{
    public function __construct(
        private readonly ImageRateService $service,
    ) {}

    public function index(Request $request)
    {
        $data = $this->service->indexData($request->only(['search', 'rate', 'sort']));

        if(in_array(Auth::user()->jabatan_id, ['10', '11', '12', '13', '19', '20', '35'])) {
            return view('pages.user.rating_image.index', $data);
        } else {
            return view('pages.admin.rating_image.index', $data);
        }
    }

    public function create(Request $request)
    {
        $intendedUrl = session()->get('url.intended');
        $nValue = null;

        
        if ($intendedUrl) {
            $parsedUrl = parse_url($intendedUrl);
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $queryParams);
                if (!empty($queryParams['id'])) {
                    $qrCode = qrCode::find($queryParams['id']);
                    $nValue = $qrCode ? explode('-', $qrCode->data)[0] : null;
                } else {
                    $nValue = $queryParams['n'] ?? null;
                }
            }
        }

        $uploadPreview = $this->service->findUploadPreviewByName($nValue);

        if($request->jenis == 'kepala-jaga') {
            return view('pages.user.rating_image.create_kepala_jaga', compact('nValue', 'uploadPreview'));
        } else {
            return view('pages.user.rating_image.create', compact('nValue', 'uploadPreview'));
        }
    }

    public function store(ImageRateStoreRequest $request)
    {
        try {
            $this->service->store($request->validated());

            return redirect()->route('rating-pekerjaan.makasih');
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (QueryException $e) {
            Log::warning('Failed to store image rating due to DB constraint.', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'error' => 'Data rating tidak valid. Silakan cek form dan coba lagi.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Unexpected error while storing image rating.', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'error' => 'Terjadi kesalahan saat menyimpan rating. Silakan coba lagi.',
            ]);
        }
    }

    public function show(ImageRate $imageRate)
    {
        return view('pages.admin.rating_image.show', compact('imageRate'));
    }

    public function edit(ImageRate $imageRate)
    {
        return view('pages.admin.rating_image.edit', compact('imageRate'));
    }

    public function update(ImageRateUpdateRequest $request, $id)
    {
        try {
            $this->service->update((int) $id, $request->validated());

            return redirect()->route('admin-rating-image.index')->with('success', 'Rating berhasil diupdate');
        } catch (QueryException $e) {
            Log::warning('Failed to update image rating due to DB constraint.', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'error' => 'Data rating tidak valid. Silakan cek form dan coba lagi.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Unexpected error while updating image rating.', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'error' => 'Terjadi kesalahan saat memperbarui rating. Silakan coba lagi.',
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->destroy((int) $id);

            return redirect()->route('admin-rating-image.index')->with('success', 'Rating berhasil dihapus');
        } catch (\Throwable $e) {
            Log::error('Unexpected error while deleting image rating.', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menghapus rating. Silakan coba lagi.',
            ]);
        }
    }
}
