<?php

namespace App\Exports;

use App\Student;
use App\Tmima;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class MathitesExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{

  public function registerEvents(): array
  {
      return [
          AfterSheet::class    => function(AfterSheet $event) {
              $event->sheet->getDelegate()->getStyle('A1:I1')->getFont()->setSize(12)->setBold(true);
              $event->sheet->getDelegate()->getStyle('A1:I1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
              $event->sheet->getDefaultRowDimension()->setRowHeight(20);
          },
      ];
  }

  public function headings(): array
  {
  return [
      'Αριθμός μητρώου',
      'Επώνυμο μαθητή',
      'Όνομα μαθητή',
      'Όνομα πατέρα',
      'Τμήματα',
      'Τμήματα',
      'Τμήματα',
      'Τμήματα',
      'Τμήματα'
  ];
}

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
      $students = Student::orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata')->get();
      $arrStudents = array();
      foreach($students as $stu){
        $arrStudents[] = [
          'id' => $stu->id,
          'eponimo' => $stu->eponimo,
          'onoma' => $stu->onoma,
          'patronimo' => $stu->patronimo,
          'tmimata' => $stu->tmimata[0] ? $stu->tmimata[0]->where('student_id', $stu->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->toArray() : []
        ];
      }

      $newStudents = array();
      foreach($arrStudents as $stu){
            $newStudents[]=[
              'id' => $stu['id'],
              'eponimo' => $stu['eponimo'],
              'onoma' => $stu['onoma'],
              'patronimo' => $stu['patronimo'],
              't1' => isset($stu['tmimata'][0])?$stu['tmimata'][0]:"",
              't2' => isset($stu['tmimata'][1])?$stu['tmimata'][1]:"",
              't3' => isset($stu['tmimata'][2])?$stu['tmimata'][2]:"",
              't4' => isset($stu['tmimata'][3])?$stu['tmimata'][3]:"",
              't5' => isset($stu['tmimata'][4])?$stu['tmimata'][4]:""
            ];
        }


        if(!$newStudents){
        $newStudents = [
          ['AM1','Επώνυμο1', 'Όνομα1', 'Πατρώνυμο1', 'τμήμα1-1', 'τμήμα1-2', 'τμήμα1-3', 'τμήμα1-4' , ''],
          ['AM2','Επώνυμο2', 'Όνομα2', 'Πατρώνυμο2', 'τμήμα2-1', 'τμήμα2-2', '', '' , ''],
          ['AM3','Επώνυμο3', 'Όνομα3', 'Πατρώνυμο3', 'τμήμα3-1', 'τμήμα3-2', 'τμήμα3-3', '' , '']
        ];
        }

        return collect($newStudents);
    }
}
