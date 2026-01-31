<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class GpsImport implements ToArray
{
    public function array(array $array): void
    {
        // Data processing handled in GpsService via Excel::toArray()
    }
}
