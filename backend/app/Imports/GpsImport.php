<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GpsImport implements ToArray, WithMultipleSheets
{
    public function array(array $array): void
    {
        // Data processing handled in GpsService via Excel::toArray()
    }

    public function sheets(): array
    {
        return [new self()];
    }
}
