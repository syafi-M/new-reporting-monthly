<?php

namespace App\Http\Controllers;

use App\Models\Latter;
use App\Models\Cover;
use App\Http\Requests\LatterRequest;
use Illuminate\Http\Request;

class ReportLettersControllers extends Controller
{
    public function index(Request $request)
    {
        $latters = Latter::with('cover')->latest()->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'data' => $latters,
            ]);
        }

        return view('latters.index', compact('latters'));
    }

    public function create()
    {
        $covers = Cover::pluck('id', 'id');
        return view('latters.create', compact('covers'));
    }

    public function store(LatterRequest $request)
    {
        $latter = Latter::create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Latter created successfully.',
                'data' => $latter,
            ], 201);
        }

        return to_route('latters.index')->with('success', 'Latter created successfully.');
    }

    public function edit(Latter $latter)
    {
        $covers = Cover::pluck('id', 'id');
        return view('latters.edit', compact('latter', 'covers'));
    }

    public function update(LatterRequest $request, Latter $latter)
    {
        $latter->update($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Latter updated successfully.',
                'data' => $latter,
            ]);
        }

        return to_route('latters.index')->with('success', 'Latter updated successfully.');
    }

    public function destroy(Request $request, Latter $latter)
    {
        $latter->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Latter deleted successfully.',
            ]);
        }

        return to_route('latters.index')->with('success', 'Latter deleted successfully.');
    }
}
