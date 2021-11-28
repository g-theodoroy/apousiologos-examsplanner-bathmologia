<?php

namespace App\Exports;

use App\Student;
use App\Tmima;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class MathitesExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
  private $tmimataMaxCount = 6;

  public function __construct()
  {
    if (Tmima::tmimataMaxCount()) $this->tmimataMaxCount = Tmima::tmimataMaxCount();
  }

  public function registerEvents(): array
  {
    $letter = range('A', 'Z')[$this->tmimataMaxCount + 3];

    return [
      AfterSheet::class    => function (AfterSheet $event) use ($letter) {
        $event->sheet->getDelegate()->getStyle('A1:' . $letter . '1')->getFont()->setSize(12)->setBold(true);
        $event->sheet->getDelegate()->getStyle('A1:' . $letter . '1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
        $event->sheet->getDefaultRowDimension()->setRowHeight(20);
      },
    ];
  }

  public function headings(): array
  {

    return array_merge(
      [
        'Αριθμός μητρώου',
        'Επώνυμο μαθητή',
        'Όνομα μαθητή',
        'Όνομα πατέρα'
      ],
      array_fill(0, $this->tmimataMaxCount, 'Τμήμα')
    );
  }

  /**
   * @return \Illuminate\Support\Collection
   */
  public function collection()
  {
    $students = Student::orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata')->get();
    $arrStudents = array();
    foreach ($students as $stu) {
      $arrStudents[] = [
        'id' => $stu->id,
        'eponimo' => $stu->eponimo,
        'onoma' => $stu->onoma,
        'patronimo' => $stu->patronimo,
        'tmimata' => $stu->tmimata[0] ? $stu->tmimata[0]->where('student_id', $stu->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->toArray() : []
      ];
    }

    $newStudents = array();
    foreach ($arrStudents as $stu) {
      $num = 1;
      $tmimata = array();
      foreach ($stu['tmimata'] as $tmi) {
        $tmimata['t' . $num] =  $tmi;
        $num++;
      }
      $newStudents[] = array_merge(
        [
          'id' => $stu['id'],
          'eponimo' => $stu['eponimo'],
          'onoma' => $stu['onoma'],
          'patronimo' => $stu['patronimo']
        ],
        $tmimata
      );
    }

    if (!$newStudents) {
      $newStudents = [
        ['100', 'Επώνυμο1', 'Όνομα1', 'Πατρώνυμο1', 'τμήμα1-1', 'τμήμα1-2', 'τμήμα1-3', 'τμήμα1-4', 'τμήμα1-5', 'τμήμα1-6'],
        ['101', 'Επώνυμο2', 'Όνομα2', 'Πατρώνυμο2', 'τμήμα2-1', 'τμήμα2-2', '', '', ''],
        ['102', 'Επώνυμο3', 'Όνομα3', 'Πατρώνυμο3', 'τμήμα3-1', 'τμήμα3-2', 'τμήμα3-3', '', '']
      ];
    }

    return collect($newStudents);
  }
}
