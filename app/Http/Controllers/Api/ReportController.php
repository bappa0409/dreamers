<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ){}

    public function summary(Request $request)
    {
        $validated=$request->validate([
            'from'=>'nullable|date',
            'to'=>'nullable|date|after_or_equal:from',
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$this->reportService->summary(
                $validated['from']??null,
                $validated['to']??null
            )
        ]);
    }
}