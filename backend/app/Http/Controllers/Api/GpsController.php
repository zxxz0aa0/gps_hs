<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GpsService;
use Illuminate\Http\Request;

class GpsController extends Controller
{
    protected $gpsService;

    public function __construct(GpsService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);

        try {
            $file = $request->file('file');
            $result = $this->gpsService->importGpsData($file);
            return response()->json([
                'message' => 'File processed successfully',
                'details' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        // /api/tracks?license_plate=ABC-123&start_date=2023-01-01
        $filters = $request->only(['license_plate', 'start_date', 'end_date']);
        $tracks = $this->gpsService->getTracks($filters);
        
        return response()->json($tracks);
    }

    public function vehicles()
    {
        $vehicles = $this->gpsService->getVehicles();
        return response()->json($vehicles);
    }
}
