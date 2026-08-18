<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }


    /*
    |--------------------------------------------------------------------------
    | All Reports
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Reports retrieved successfully.',

            'data' => [
                'members' =>
                    $this->reportService->memberReport(),

                'finance' =>
                    $this->reportService->financeReport(),

                'investments' =>
                    $this->reportService->investmentReport(),

                'land' =>
                    $this->reportService->landReport(),

                'projects' =>
                    $this->reportService->projectReport(),

                'polls' =>
                    $this->reportService->pollReport(),
            ]
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Individual Reports
    |--------------------------------------------------------------------------
    */

    public function members(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->memberReport()
        ]);
    }


    public function finance(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->financeReport()
        ]);
    }


    public function investments(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->investmentReport()
        ]);
    }


    public function land(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->landReport()
        ]);
    }


    public function projects(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->projectReport()
        ]);
    }


    public function polls(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->pollReport()
        ]);
    }
}