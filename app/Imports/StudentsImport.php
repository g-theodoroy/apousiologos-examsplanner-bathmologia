<?php

namespace App\Imports;

use App\Student;
use App\Tmima;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class StudentsImport implements OnEachRow, WithStartRow, WithValidation, SkipsOnFailure
{
  use Importable, SkipsFailures;

  protected static $student_id;
  protected static $unique_student_id_tmima;
  protected static $students = 0;
  protected static $tmimata = 0;

  public function startRow(): int
  {
    return 2;
  }

  public function onRow(Row $row)
  {
    $row = $row->toArray();
    self::$students++;
    $student = Student::updateOrCreate(['id' => trim($row[0])], [
      'eponimo' => trim($row[1]),
      'onoma' => trim($row[2]),
      'patronimo' => trim($row[3]),
    ]);

    $n = 1;
    while (isset($row[3 + $n]) && trim($row[3 + $n])) {
      self::$tmimata++;
      Tmima::updateOrCreate(['student_id' => $student->id, 'tmima' => trim($row[3 + $n])], [
        'student_id' => $student->id,
        'tmima' => trim($row[3 + $n]),
      ]);
      $n++;
    }
  }

  public function getStudentsCount()
  {
    return self::$students;
  }
  public function getTmimataCount()
  {
    return self::$tmimata;
  }

  public function rules(): array
  {
    return [
      '0' => 'integer|required|unique:students,id',
      '*.0' => 'integer|required|unique:students,id',
      '1' => 'required',
      '*.1' => 'required',
      '2' => 'required',
      '*.2' => 'required'
    ];
  }

  public function customValidationAttributes()
  {
    return [
      '0' => 'Αρ.Μητρώου',
      '1' => 'Επώνυμο',
      '2' => 'Όνομα'
    ];
  }
}
