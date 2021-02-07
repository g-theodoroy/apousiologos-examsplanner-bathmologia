<?php

namespace App\Exports;

use App\User;
use App\Role;
use App\Anathesi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class KathigitesExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
  /**
   * @return \Illuminate\Support\Collection
   */

  public function registerEvents(): array
  {
    return [
      AfterSheet::class    => function (AfterSheet $event) {
        $event->sheet->getDelegate()->getStyle('A1:F1')->getFont()->setSize(12)->setBold(true);
        $event->sheet->getDelegate()->getStyle('A1:F1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
        $event->sheet->getDefaultRowDimension()->setRowHeight(20);
      },
    ];
  }

  public function headings(): array
  {
    return [
      'Επώνυμο',
      'Όνομα',
      'Email',
      'password',
      'Τμήμα',
      'Μάθημα'
    ];
  }

  public function collection()
  {
    $kathigites = User::whereRoleId(Role::whereRole('Καθηγητής')->first()->id)->orderby('name')->with('anatheseis')->get()->toArray();

    $arrKathigites = array();
    foreach ($kathigites as $kath) {
      $data = explode(' ', $kath['name'], 2);
      usort($kath['anatheseis'], function ($a, $b) {
        return strnatcasecmp($a['tmima'],  $b['tmima']);
      });

      $num = count($kath['anatheseis']);
      for ($index = 0; $index < $num; $index++) {
        if ($index == 0) {
          $arrKathigites[] = [
            'eponimo' => $data[0],
            'onoma' => $data[1],
            'email' => $kath['email'],
            'password' => '',
            'tmima' => $kath['anatheseis'][$index]['tmima'],
            'mathima' => $kath['anatheseis'][$index]['mathima']
          ];
        } else {
          $arrKathigites[] = [
            'eponimo' => '',
            'onoma' => '',
            'email' => '',
            'password' => '',
            'tmima' => $kath['anatheseis'][$index]['tmima'] ,
            'mathima' => $kath['anatheseis'][$index]['mathima'] 
          ];
        }
      }
    }



    if (!$arrKathigites) {
      $arrKathigites = [
        ['Επώνυμο1', 'Όνομα1', 'email1', 'password1', 'τμήμα1-1', 'μάθημα1-1'],
        ['', '', '', '', 'τμήμα1-2', 'μάθημα1-2'],
        ['', '', '', '', 'τμήμα1-3', 'μάθημα1-3'],
        ['', '', '', '', 'τμήμα1-4', 'μάθημα1-4'],
        ['Επώνυμο2', 'Όνομα2', 'email2', 'password2', 'τμήμα2-1', 'μάθημα2-1'],
        ['', '', '', '', 'τμήμα2-2', 'μάθημα2-2'],
        ['', '', '', '', 'τμήμα2-3'], 'μάθημα2-3'
      ];
    }
    return collect($arrKathigites);
  }
}
