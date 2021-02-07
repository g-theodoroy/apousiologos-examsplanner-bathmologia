<?php

namespace App\Imports;

use App\Program;
use Maatwebsite\Excel\Concerns\ToModel;
Use Maatwebsite\Excel\Concerns\WithStartRow;

class ProgramImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function startRow(): int {
         return 2;
    }

    public function model(array $row)
    {
        return new Program([
          'id' => trim($row[0]),
          'start' => preg_replace('/\D/', '',trim($row[1])),
          'stop' => preg_replace('/\D/', '',trim($row[2])),
        ]);
    }
}
