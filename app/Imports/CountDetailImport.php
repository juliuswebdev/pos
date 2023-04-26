<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

use App\CountDetail;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CountDetailImport implements ToModel, WithHeadingRow
{

    public $id;
  
    function __construct($id) {
        $this->id = $id;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new CountDetail([
            'count_header_id' => $this->id,
            'sku' => $row['sku'], 
            'upc' => $row['upc_code'] ?? null, 
            'frozen_quantity' => $row['expected'] ?? 0, 
            'count_quantity' => $row['counted'] ?? 0
        ]);
    }
}
