<?php

namespace App\Imports;

use App\Student;
use App\Tmima;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
Use Maatwebsite\Excel\Concerns\WithStartRow;

class StudentsImport implements OnEachRow, WithStartRow
{
  protected static $student_id;
  protected static $unique_student_id_tmima;
  protected static $students = 0;
  protected static $tmimata = 0;

    public function startRow(): int {
         return 2;
    }

    public function onRow(Row $row)
    {
        $row = $row->toArray();
          self::$students++;
          $student = Student::updateOrCreate(['id' => trim($row[0])],[
            'eponimo' => trim($row[1]),
            'onoma' => trim($row[2]),
            'patronimo' => trim($row[3]),
            ]);

            $n = 1;
            while (trim($row[3+$n])){
              self::$tmimata++;
              Tmima::updateOrCreate(['student_id' => $student->id, 'tmima' => trim($row[3+$n]) ],[
                'student_id' => $student->id,
                'tmima' => trim($row[3+$n]),
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
}
