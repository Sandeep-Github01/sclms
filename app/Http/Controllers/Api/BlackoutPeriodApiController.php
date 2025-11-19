<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlackoutPeriod;
use App\Services\BlackoutPeriodService;
use Illuminate\Http\Request;

class BlackoutPeriodApiController extends Controller
{
    protected $service;

    public function __construct(BlackoutPeriodService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json(BlackoutPeriod::all());
    }

    public function store(Request $request)
    {
        return response()->json($this->service->create($request->all()));
    }

    public function update(Request $request, BlackoutPeriod $blackoutPeriod)
    {
        return response()->json($this->service->update($blackoutPeriod, $request->all()));
    }

    public function destroy(BlackoutPeriod $blackoutPeriod)
    {
        $blackoutPeriod->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
