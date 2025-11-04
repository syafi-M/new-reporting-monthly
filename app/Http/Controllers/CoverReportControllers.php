<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Request\CoverRequest;
use App\Helpers\FileHelper;
use App\Models\Cover;

class CoverReportControllers extends Controller
{
    public function index(Request $request)
    {
        $covers = Cover::with('client')->latest()->paginate(10);

        // If AJAX → return JSON data
        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'data' => $covers
            ]);
        }

        // normal view
        return view('covers.index', compact('covers'));
    }

    public function show(Request $request, $id)
    {
        try {
            $covers = Cover::with('client')->first($id);

            // If AJAX → return JSON data
            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Get cover by ID.',
                    'data' => $covers
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'data' => $e
            ], 500);
        }

    }

    public function store(CoverRequest $request)
    {
        $data = $request->validated();

        // Handle Image Upload With Helper
        $validate['img_src_1'] = FileHelper::uploadImage($request->file('img_src_1'), 'sac-banner');

        $validate['img_src_2'] = FileHelper::uploadImage($request->file('img_src_2'), 'mitra-banner');

        Cover::create($validate);

        // Return JSON if AJAX
        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Cover created successfully.',
                'data' => $cover
            ], 201);
        }

        // Normal redirect
        return to_route('covers.index')->with('success', 'Cover created successfully.');

    }

    public function update(CoverRequest $request, Cover $cover)
    {
        $data = $request->validated();

        $data['img_src_1'] = FileHelper::uploadImage($request->file('img_src_1'), 'covers', $cover->img_src_1);
        $data['img_src_2'] = FileHelper::uploadImage($request->file('img_src_2'), 'covers', $cover->img_src_2);

        $cover->update($data);

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Cover updated successfully.',
                'data' => $cover
            ]);
        }

        return to_route('covers.index')->with('success', 'Cover updated successfully.');
    }

    public function destroy(Request $request, Cover $cover)
    {
        if ($cover->img_src_1) \Storage::disk('public')->delete($cover->img_src_1);
        if ($cover->img_src_2) \Storage::disk('public')->delete($cover->img_src_2);
        $cover->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Cover deleted successfully.'
            ]);
        }

        return to_route('covers.index')->with('success', 'Cover deleted successfully.');
    }
    
}
