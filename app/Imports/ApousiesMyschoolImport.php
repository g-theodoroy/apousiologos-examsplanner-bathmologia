<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
Use Maatwebsite\Excel\Concerns\WithStartRow;
use \PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Program;
use App\Apousie;
use Carbon\Carbon;

class ApousiesMyschoolImport implements OnEachRow, WithStartRow
{
  protected static $am = '';
  protected static $studentsApousies = 0;
  protected static $daysWithApousies = 0;

  public function startRow(): int {
       return 2;
  }

  public function onRow(Row $row)
  {
      $row      = $row->toArray();
      if(trim($row[1])){
         self::$am = trim($row[1]);
        self::$studentsApousies++;
      }

      $program = new Program;
      // οι ώρες του προγράμματος
      $totalHours = $program->get_num_of_hours();

      if(trim($row[4]) && trim($row[5])){
        $date = Carbon::instance(Date::excelToDateTimeObject(trim($row[4])))->format("Ymd");
        $apousies = str_repeat("0", $totalHours);
        $numOfDay = trim($row[5]);
        self::$daysWithApousies++;

        $dayApousies = Apousie::where('student_id', self::$am )->where('date', $date)->first();
        if (! $dayApousies){
          for($i=0; $i<$numOfDay; $i++){
            $apousies = substr_replace ( $apousies, '1', $i, 1 );
          }
          Apousie::create([
            'student_id' => self::$am,
            'date' => $date,
            'apousies' => $apousies,
          ]);
        }else{
          $apousies = $dayApousies->apousies;
          $sumOfInserted = array_sum(preg_split("//", $apousies ));
          if($numOfDay > $sumOfInserted){
            //self::$daysWithApousies++;
            $diff = $numOfDay - $sumOfInserted;
            $changed = 0;
            for($i = 0 ; $i<$totalHours ; $i++){
              if(substr($apousies, $i, 1) == '0' && $changed < $diff){
                $apousies = substr_replace ( $apousies, '1', $i, 1 );
                $changed++;
              }
            }
          }
          if($numOfDay < $sumOfInserted){
            //self::$daysWithApousies++;
            $diff = $sumOfInserted - $numOfDay ;
            $changed = 0;
            for($i = $totalHours; $i > 0 ; $i--){
              if( substr($apousies, $i-1, 1) == '1' && $changed < $diff){
                $apousies = substr_replace ( $apousies, '0', $i-1, 1 );
                $changed++;
              }
            }
            $dayApousies->apousies = $apousies;
            $dayApousies->save();
          }
        }
      }
  }

  public function getStudentsApousiesCount()
  {
      return self::$studentsApousies;
  }
  public function getDaysApousiesCount()
  {
      return self::$daysWithApousies;
  }

}
