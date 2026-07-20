<?php

namespace App\Http\Controllers;

use App\Http\Requests\FixedImageRateRequest;
use App\Services\Monitoring\FixedImageService;
use Exception;
use Illuminate\Http\Request;

class FixedImageController extends Controller
{
    public function __construct(
        private readonly FixedImageService $service,
    ) {}

    public function index()
    {
        return view('pages.user.set_fixed.index', $this->service->indexData(auth()->user()));
    }

    public function create(Request $request)
    {
        try {
            $data = $this->service->createData($request);

            return response()->json([
                'status' => true,
                'message' => 'Get All Required Data',
                'data' => $data,
            ], 200);
        } catch (Exception $e) {
            throw new Exception('Error Processing Request'.$e->getMessage(), 1);
        }
    }

    public function store(Request $request)
    {
        try {
            $result = $this->service->setImage($request->all(), $request);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'limit' => $result['limit'] ?? false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'limit' => false,
                'message' => $result['message'],
                'data' => $result['data'],
                'counts' => $result['counts'],
                'fixed_state' => [
                    'upload_image_id' => (int) $result['data']->upload_image_id,
                    'is_fixed' => true,
                    'verified_by' => auth()->user()?->nama_lengkap,
                    'rating_value' => $result['rating']['rating_value'] ?? null,
                    'rating_reason' => $result['rating']['rating_reason'] ?? null,
                    'rated_by_name' => $result['rating']['rated_by_name'] ?? null,
                    'rated_by_user_id' => $result['rating']['rated_by_user_id'] ?? null,
                    'can_edit_rating' => $result['rating']['can_edit_rating'] ?? null,
                ],
            ], 200);
        } catch (Exception $e) {
            throw new Exception('Error Processing Request'.$e->getMessage(), 1);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $val = $this->service->removeSelection((int) $id, $request);

            if (! $val['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $val['message'],
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => $val['message'],
                'counts' => $val['counts'],
                'fixed_state' => [
                    'upload_image_id' => (int) $id,
                    'is_fixed' => false,
                    'verified_by' => null,
                ],
            ], 200);
        } catch (Exception $e) {
            throw new Exception('Error Processing Request'.$e->getMessage(), 1);
        }
    }

    public function getCountFixed(Request $request)
    {
        $counts = $this->service->countFixed($request);

        return response()->json([
            'status' => true,
            'message' => 'Get Counting FixedImage',
            'data' => $counts,
        ], 200);
    }

    public function rate(FixedImageRateRequest $request)
    {
        try {
            $result = $this->service->rateImage($request->validated(), $request);

            if (! $result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                ], $result['code'] ?? 422);
            }

            return response()->json([
                'status' => true,
                'message' => $result['message'],
                'data' => $result['data'],
            ], 200);
        } catch (Exception $e) {
            throw new Exception('Error Processing Request'.$e->getMessage(), 1);
        }
    }
}
