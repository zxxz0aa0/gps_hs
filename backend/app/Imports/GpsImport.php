<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class GpsImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        // This method is required by the interface but we handle logic in the Service
        // using Excel::toArray() to support flexible multi-sheet processing.
    }
}
