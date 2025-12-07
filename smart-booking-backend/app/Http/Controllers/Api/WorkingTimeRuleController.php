<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkingTimeRuleRequest;
use App\Models\WorkingTimeRule;

class WorkingTimeRuleController extends Controller
{
    public function index()
    {
        return WorkingTimeRule::orderBy('date')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function store(StoreWorkingTimeRuleRequest $request)
    {
        if (!$request->filled('day_of_week') && !$request->filled('date')) {
            return response()->json([
                'message' => 'Either day_of_week or date must be provided.',
            ], 422);
        }

        $data = $request->validated();

        $rule = WorkingTimeRule::create($data);

        return response()->json([
            'message' => 'Working time rule created.',
            'data' => $rule,
        ], 201);
    }
}
