<?php

namespace App\Imports;

use App\Program;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ProgramImport implements ToModel, WithStartRow, WithValidation, SkipsOnFailure
{
  use Importable, SkipsFailures;

  /**
   * @param array $row
   *
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  public function startRow(): int
  {
    return 2;
  }

  public function model(array $row)
  {
    return new Program([
      'id' => trim($row[0]),
      'start' => preg_replace('/\D/', '', trim($row[1])),
      'stop' => preg_replace('/\D/', '', trim($row[2])),
    ]);
  }

  public function rules(): array
  {
    return [
      '0' => 'integer|required',
      '*.0' => 'integer|required',
      '1' => 'integer|required',
      '*.1' => 'integer|required',
      '2' => 'integer|required',
      '*.2' => 'integer|required'
    ];
  }

  public function customValidationAttributes()
  {
    return [
      '0' => 'Ώρα',
      '1' => 'Έναρξη',
      '2' => 'Λήξη'
    ];
  }
}
