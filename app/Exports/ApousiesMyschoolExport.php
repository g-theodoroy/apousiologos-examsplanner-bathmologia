<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ApousiesMyschoolExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{

  public function registerEvents(): array
  {
      return [
          AfterSheet::class    => function(AfterSheet $event) {
              $event->sheet->getDelegate()->getStyle('A1:F1')->getFont()->setSize(12)->setBold(true);
              $event->sheet->getDelegate()->getStyle('A1:F1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
              $event->sheet->getDelegate()->getStyle('B1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFFF33');
              $event->sheet->getDelegate()->getStyle('E1:F1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFFF33');
              $event->sheet->getDefaultRowDimension()->setRowHeight(20);
          },
      ];
  }

  public function headings(): array
  {
  return [
      'Α/Α (προαιρετικά)',
      'Αρ. μητρώου',
      'Επώνυμο μαθητή (προαιρετικά)',
      'Όνομα μαθητή (προαιρετικά)',
      'Ημ/νία',
      'Σύνολο απουσιών'
  ];
}

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect([]);//
    }
}
