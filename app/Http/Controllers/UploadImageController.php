<?php

namespace App\Http\Controllers;

use App\Models\UploadImage;
use App\Http\Requests\UImageUserRequest;
use Illuminate\Http\Request;
use App\Helpers\FileHelper;
use Carbon\Carbon;

class UploadImageController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $images = UploadImage::where('clients_id', $user->clients_id)
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(14);

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'data' => $images,
            ]);
        }

        return view('upload_images.index', compact('images'));
    }

    public function store(UImageUserRequest $request)
    {
        $user = $request->user();

        // Monthly limit
        $uploadCount = UploadImage::where('clients_id', $user->clients_id)
            ->where('user_id', $user->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        if ($uploadCount >= 14) {
            $message = 'Maksimal Upload Data sudah terpenuhi coba lagi bulan depan.';

            return $request->ajax()
                ? response()->json(['message' => $message], 403)
                : back()->with('error', $message);
        }

        $img_before   = FileHelper::uploadImage($request->file('img_before'), 'upload_images/before');
        $img_proccess = FileHelper::uploadImage($request->file('img_proccess'), 'upload_images/process');
        $img_final    = FileHelper::uploadImage($request->file('img_final'), 'upload_images/final');

        $upload = UploadImage::create([
            'user_id'      => $user->id,
            'clients_id'   => $user->clients_id,
            'img_before'   => $img_before,
            'img_proccess' => $img_proccess,
            'img_final'    => $img_final,
            'note'         => $request->note,
            'max_data'     => 14,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Upload created successfully',
                'data' => $upload,
            ], 201);
        }

        return redirect()->route('upload-images.index')->with('success', 'Upload created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, UploadImage $uploadImage)
    {
        $user = auth()->user();

        if ($uploadImage->clients_id !== $user->clients_id || $uploadImage->user_id !== $user->id) {
            $message = 'Unauthorized';

            return $request->ajax()
                ? response()->json(['message' => $message], 403)
                : abort(403, $message);
        }

         if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Get Data By ID',
                'data' => $uploadImage,
            ], 201);
        }

        return view('upload_images.show', compact('uploadImage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UImageUserRequest $request, UploadImage $uploadImage)
    {
        $user = $request->user();

        if ($uploadImage->clients_id !== $user->clients_id || $uploadImage->user_id !== $user->id) {
            $message = 'Unauthorized';

            return $request->ajax()
                ? response()->json(['message' => $message], 403)
                : abort(403, $message);
        }

        $img_before   = FileHelper::uploadImage($request->file('img_before'), 'upload_images/before', $uploadImage->img_before);
        $img_proccess = FileHelper::uploadImage($request->file('img_proccess'), 'upload_images/process', $uploadImage->img_proccess);
        $img_final    = FileHelper::uploadImage($request->file('img_final'), 'upload_images/final', $uploadImage->img_final);

        $uploadImage->update([
            'img_before'   => $img_before,
            'img_proccess' => $img_proccess,
            'img_final'    => $img_final,
            'note'         => $request->note,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Upload updated successfully',
                'data' => $uploadImage,
            ]);
        }

        return redirect()->route('upload-images.index')->with('success', 'Upload updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, UploadImage $uploadImage)
    {
        $user = $request->user();

        if ($uploadImage->clients_id !== $user->clients_id || $uploadImage->user_id !== $user->id) {
            $message = 'Unauthorized';

            return $request->ajax()
                ? response()->json(['message' => $message], 403)
                : abort(403, $message);
        }

        if ($uploadImage->img_before) \Storage::disk('public')->delete($uploadImage->img_before);
        if ($uploadImage->img_proccess) \Storage::disk('public')->delete($$uploadImage->img_proccess);
        if ($uploadImage->img_final) \Storage::disk('public')->delete($uploadImage->img_final);

        $uploadImage->delete();

        if ($request->ajax()) {
            return response()->json(['message' => 'Upload deleted successfully']);
        }

        return redirect()->route('upload-images.index')->with('success', 'Upload deleted successfully.');
    }
}
