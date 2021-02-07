<?php

namespace App\Exports;

use App\Program;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProgramExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{

  public function registerEvents(): array
  {
      return [
          AfterSheet::class    => function(AfterSheet $event) {
              $event->sheet->getDelegate()->getStyle('A1:C1')->getFont()->setSize(12)->setBold(true);
              $event->sheet->getDelegate()->getStyle('A1:C1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
              $event->sheet->getDefaultRowDimension()->setRowHeight(20);
          },
      ];
  }

  public function headings(): array
  {
  return [
      'ΩΡΑ',
      'ΑΡΧΗ',
      'ΤΕΛΟΣ'
  ];
}

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Program::all('id','start', 'stop');
    }

}
