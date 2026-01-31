<?php

namespace App\Services;

use App\Imports\GpsImport;
use App\Models\GpsRecord;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class GpsService
{
    private const BATCH_SIZE = 500;
    private const DEFAULT_PER_PAGE = 1000;

    /**
     * Import GPS records from Excel file
     */
    public function importGpsData($file): array
    {
        $sheets = Excel::toArray(new GpsImport, $file);

        $totalInserted = 0;
        $totalSkipped = 0;

        DB::beginTransaction();
        try {
            foreach ($sheets as $sheetIndex => $rows) {
                if (count($rows) < 4) {
                    continue;
                }

                $vehicleInfoRow = $rows[1] ?? [];
                $rowString = implode(' ', array_filter($vehicleInfoRow, fn($val) => !is_null($val)));
                $vehicleData = $this->parseVehicleInfo($rowString);

                if (empty($vehicleData['license_plate'])) {
                    $vehicleData['license_plate'] = 'Unknown';
                }

                $recordsToInsert = [];

                for ($i = 3; $i < count($rows); $i++) {
                    $row = $rows[$i];

                    $rawTime = $row[0] ?? null;
                    $location = $row[1] ?? null;
                    $longitude = $row[7] ?? null;
                    $latitude = $row[8] ?? null;

                    if (!$rawTime) continue;

                    // Validate coordinates
                    if (!$this->isValidCoordinate($longitude, $latitude)) {
                        $totalSkipped++;
                        continue;
                    }

                    try {
                        $parsedDateTime = $this->parseDateTime($rawTime);
                    } catch (\Exception $e) {
                        $totalSkipped++;
                        continue;
                    }

                    $recordsToInsert[] = [
                        'license_plate' => $vehicleData['license_plate'],
                        'driver_name' => $vehicleData['driver_name'],
                        'fleet_number' => $vehicleData['fleet_number'],
                        'date' => $parsedDateTime['date'],
                        'time' => $parsedDateTime['time'],
                        'location' => $location,
                        'longitude' => (float) $longitude,
                        'latitude' => (float) $latitude,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($recordsToInsert) >= self::BATCH_SIZE) {
                        $inserted = $this->insertIgnoreDuplicates($recordsToInsert);
                        $totalInserted += $inserted;
                        $totalSkipped += count($recordsToInsert) - $inserted;
                        $recordsToInsert = [];
                    }
                }

                if (!empty($recordsToInsert)) {
                    $inserted = $this->insertIgnoreDuplicates($recordsToInsert);
                    $totalInserted += $inserted;
                    $totalSkipped += count($recordsToInsert) - $inserted;
                }
            }

            DB::commit();
            return [
                'status' => 'success',
                'inserted' => $totalInserted,
                'skipped' => $totalSkipped
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Insert records, ignoring duplicates based on unique constraint
     */
    private function insertIgnoreDuplicates(array $records): int
    {
        $countBefore = GpsRecord::count();
        GpsRecord::insertOrIgnore($records);
        $countAfter = GpsRecord::count();

        return $countAfter - $countBefore;
    }

    /**
     * Validate longitude and latitude ranges
     */
    private function isValidCoordinate($longitude, $latitude): bool
    {
        if (!is_numeric($longitude) || !is_numeric($latitude)) {
            return false;
        }

        $lng = (float) $longitude;
        $lat = (float) $latitude;

        return $lng >= -180 && $lng <= 180 && $lat >= -90 && $lat <= 90;
    }

    private function parseVehicleInfo(string $text): array
    {
        $info = [
            'license_plate' => null,
            'driver_name' => null,
            'fleet_number' => null
        ];

        // Format: 車牌及駕駛人TDU-7330,柯清龍5193
        if (preg_match('/車牌及駕駛人\s*([A-Z0-9\-]+)\s*,\s*([\p{L}]+)(\d+)/u', $text, $matches)) {
            $info['license_plate'] = trim($matches[1]);
            $info['driver_name'] = trim($matches[2]);
            $info['fleet_number'] = trim($matches[3]);
            return $info;
        }

        // Fallback: original patterns
        if (preg_match('/(?:車牌(?:號碼)?|License|Plate)[:\s：]*([A-Z0-9\-]+)/ui', $text, $matches)) {
            $info['license_plate'] = trim($matches[1]);
        }

        if (preg_match('/(?:駕駛(?:人|員)?|Driver)[:\s：]*([\p{L}\s]+)/u', $text, $matches)) {
            $info['driver_name'] = trim($matches[1]);
        }

        if (preg_match('/(?:車隊(?:編號)?|Fleet)[:\s：]*([0-9A-Z]+)/ui', $text, $matches)) {
            $info['fleet_number'] = trim($matches[1]);
        }

        return $info;
    }

    private function parseDateTime($value): array
    {
        if (is_numeric($value)) {
            $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            return [
                'date' => $dt->format('Y-m-d'),
                'time' => $dt->format('H:i:s')
            ];
        }

        $dt = Carbon::parse($value);
        return [
            'date' => $dt->format('Y-m-d'),
            'time' => $dt->format('H:i:s')
        ];
    }

    /**
     * Get tracks with optional pagination
     */
    public function getTracks(array $filters, ?int $perPage = null)
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

        $query->orderBy('date')->orderBy('time');

        if ($perPage) {
            return $query->paginate($perPage);
        }

        // Default: limit to prevent memory issues
        return $query->limit(self::DEFAULT_PER_PAGE)->get();
    }

    public function getVehicles()
    {
        return GpsRecord::select('license_plate', 'driver_name', 'fleet_number')
            ->distinct()
            ->orderBy('license_plate')
            ->get();
    }
}
