<?php

namespace App\Services;

use App\Imports\GpsImport;
use App\Models\GpsRecord;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class GpsService
{
    /**
     * Import GPS records from Excel file
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     */
    public function importGpsData($file)
    {
        // Parse all sheets as array
        $sheets = Excel::toArray(new GpsImport, $file);
        
        $totalInserted = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($sheets as $sheetIndex => $rows) {
                // Skip empty sheets or sheets with insufficient rows
                if (count($rows) < 4) {
                    continue;
                }

                // 1. Parse Vehicle Info from Row 2 (Index 1)
                $vehicleInfoRow = $rows[1] ?? [];
                // Convert row to string for regex searching if it's spread across cells
                $rowString = implode(' ', array_filter($vehicleInfoRow, function($val) { return !is_null($val); }));
                
                $vehicleData = $this->parseVehicleInfo($rowString);
                
                // If parsing fails, store error or skip? 
                // For now, use defaults or skip line. 
                // Suggestion: If critical info missing, maybe throw exception?
                if (empty($vehicleData['license_plate'])) {
                    // Try to find in specific cells if regex failed?
                    // Assuming columns: A: License, B: Driver? (No, document says logic Step 3.2 section 2)
                    // "2nd Row: Parse license, driver, fleet"
                    // We'll trust the Regex or add fallback logic here.
                    $vehicleData['license_plate'] = 'Unknown'; 
                }

                // 2. Parse Tracks from Row 4 (Index 3) onwards
                $recordsToInsert = [];
                $sheetDate = null; // Try to determine date from data?

                for ($i = 3; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    // Column mapping assumption based on typical GPS logs:
                    // 0: Time, 1: Location, 2: Lat/Lon? 
                    // Document says: "Row 4+: Read time, location, longitude, latitude"
                    // We will assume indices: 0 => Time, 1 => Location, 2 => Longitude, 3 => Latitude (or similar)
                    // NOTE: ADJUST INDICES BASED ON ACTUAL EXCEL IF NEEDED.
                    // For now assuming A, B, C, D order as listed in text.
                    
                    $rawTime = $row[0] ?? null;
                    $location = $row[1] ?? '';
                    $longitude = $row[2] ?? 0;
                    $latitude = $row[3] ?? 0;

                    if (!$rawTime) continue;

                    // Parse Date and Time
                    // "Split positioning time into date and time"
                    // If $rawTime is Excel serial, convert key.
                    // If string "2023-01-01 10:00", split.
                    
                    try {
                        $parsedDateTime = $this->parseDateTime($rawTime);
                        $date = $parsedDateTime['date'];
                        $time = $parsedDateTime['time'];
                    } catch (\Exception $e) {
                         // Fallback or skip
                         continue;
                    }

                    $recordsToInsert[] = [
                        'license_plate' => $vehicleData['license_plate'],
                        'driver_name' => $vehicleData['driver_name'] ?? '',
                        'fleet_number' => $vehicleData['fleet_number'] ?? '',
                        'date' => $date,
                        'time' => $time,
                        'location' => $location,
                        'longitude' => $longitude, // Ensure format
                        'latitude' => $latitude,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Batch insert every 500 records
                    if (count($recordsToInsert) >= 500) {
                        GpsRecord::insert($recordsToInsert);
                        $totalInserted += count($recordsToInsert);
                        $recordsToInsert = [];
                    }
                }

                // Insert remaining
                if (!empty($recordsToInsert)) {
                    GpsRecord::insert($recordsToInsert);
                    $totalInserted += count($recordsToInsert);
                }
            }
            
            DB::commit();
            return ['status' => 'success', 'count' => $totalInserted];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function parseVehicleInfo($text)
    {
        // Example text: "車牌號碼: ABC-1234 駕駛人: 王小明 車隊編號: 888"
        // Regex needs to be flexible.
        $info = [
            'license_plate' => null,
            'driver_name' => null,
            'fleet_number' => null
        ];

        // Matches: "車牌: <Value>" or "License: <Value>"
        // Supports Chinese colon and space
        if (preg_match('/(?:車牌(?:號碼)?|License|Plate)[:\s]*([A-Z0-9\-]+)/ui', $text, $matches)) {
            $info['license_plate'] = trim($matches[1]);
        }

        if (preg_match('/(?:駕駛(?:人|員)?|Driver)[:\s]*([\p{L}\s]+)/u', $text, $matches)) {
            $info['driver_name'] = trim($matches[1]);
        }

        if (preg_match('/(?:車隊(?:編號)?|Fleet)[:\s]*([0-9A-Z]+)/ui', $text, $matches)) {
            $info['fleet_number'] = trim($matches[1]);
        }

        return $info;
    }

    private function parseDateTime($value)
    {
        // Use Maatwebsite helper if simple, but here specific logic:
        try {
            if (is_numeric($value)) {
                // Excel numeric date
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return [
                    'date' => $dt->format('Y-m-d'),
                    'time' => $dt->format('H:i:s')
                ];
            }
            
            // Try parsing string with Carbon
            $dt = Carbon::parse($value);
            return [
                'date' => $dt->format('Y-m-d'),
                'time' => $dt->format('H:i:s')
            ];
        } catch (\Exception $e) {
             // Handle "Time only" case? Assuming date exists in string or handled elsewhere.
             // If ONLY time is present "10:00:00", Carbon::parse uses today's date.
             // This might be risky. 
             // Phase 2 MVP: Assume datetime is complete.
             throw $e;
        }
    }

    public function getTracks($filters)
    {
        $query = GpsRecord::query();

        if (!empty($filters['license_plate'])) {
            $query->where('license_plate', $filters['license_plate']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('date', '<=', $filters['end_date']);
        }
        
        // Optimize: verify index usage in Review
        return $query->orderBy('date')->orderBy('time')->get();
    }

    public function getVehicles()
    {
        return GpsRecord::select('license_plate', 'driver_name', 'fleet_number')
            ->distinct()
            ->get();
    }
}
