<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Attributes\Middleware;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    #[Middleware(['auth:sanctum', 'role:admin'])]
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $startDate = Carbon::parse($request->date_from)->startOfDay();
        $endDate = Carbon::parse($request->date_to)->endOfDay();

        $stats = $this->reportService->getStats($startDate, $endDate);

        return response()->json($stats);
    }
}
