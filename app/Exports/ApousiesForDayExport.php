<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Student;
use Carbon\Carbon;

class ApousiesForDayExport implements FromView, ShouldAutoSize, WithEvents
{
    public $apoDate;
    public $eosDate;

    public function __construct($apoDate = "", $eosDate = "")
    {
        $this->apoDate = $apoDate;
        $this->eosDate = $eosDate;
    }

    public function registerEvents(): array
    {
        return [
          AfterSheet::class    => function (AfterSheet $event) {
              $event->sheet->getDelegate()->getStyle('A1:J1')->getFont()->setSize(12)->setBold(true);
              $event->sheet->getDelegate()->getStyle('A1:J1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
              $event->sheet->getDelegate()->getStyle('B1:D1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFFF33');
              $event->sheet->getDelegate()->getStyle('F1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFFF33');
              $event->sheet->getDelegate()->getStyle('J1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFFF33');
              $event->sheet->getDefaultRowDimension()->setRowHeight(20);
          },
      ];
    }

    public function view(): View
    {
        if ($this->apoDate) {
            $apoDate = Carbon::createFromFormat("!d/m/y", $this->apoDate)->format("Ymd");
        }
        if ($this->eosDate) {
            $eosDate = Carbon::createFromFormat("!d/m/y", $this->eosDate)->format("Ymd");
        }
        $nowDate = Carbon::now()->format("Ymd");
        $arrStudents = array();

        if ($apoDate && $eosDate) {
            // βρίσκω τους μαθητές που έχουν απουσίες την συγκεκριμμένη ημέρα
            $students = Student::whereHas('apousies', function ($query) use ($apoDate, $eosDate) {
                $query->where('date', '>=', $apoDate)->where('date', '<=', $eosDate);
            })->orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata')->with('apousies')->get();

            foreach ($students as $stuApFoD) {
                $arrStudents[] = [
          'id' => $stuApFoD->id,
          'eponimo' => $stuApFoD->eponimo,
          'onoma' => $stuApFoD->onoma,
          'patronimo' => $stuApFoD->patronimo,
          'tmima' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->first('tmima')->tmima,
          'tmimata' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->implode(', '),
          'apousies' => $stuApFoD->apousies[0]->where('student_id', $stuApFoD->id)->where('date', '>=', $apoDate)->where('date', '<=', $eosDate)->orderby('date')->pluck('apousies', 'date')->toArray(),
        ];
            }
        } elseif ($apoDate) {
            // βρίσκω τους μαθητές που έχουν απουσίες την συγκεκριμμένη ημέρα
            $students = Student::whereHas('apousies', function ($query) use ($apoDate) {
                $query->where('date', '>=', $apoDate);
            })->orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata')->with('apousies')->get();

            foreach ($students as $stuApFoD) {
                $arrStudents[] = [
          'id' => $stuApFoD->id,
          'eponimo' => $stuApFoD->eponimo,
          'onoma' => $stuApFoD->onoma,
          'patronimo' => $stuApFoD->patronimo,
          'tmima' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->first('tmima')->tmima,
          'tmimata' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->implode(', '),
          'apousies' => $stuApFoD->apousies[0]->where('student_id', $stuApFoD->id)->where('date', '>=', $apoDate)->orderby('date')->pluck('apousies', 'date')->toArray(),
        ];
            }
        } elseif ($eosDate) {
            // βρίσκω τους μαθητές που έχουν απουσίες την συγκεκριμμένη ημέρα
            $students = Student::whereHas('apousies', function ($query) use ($eosDate) {
                $query->where('date', '<=', $eosDate);
            })->orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata')->with('apousies')->get();

            foreach ($students as $stuApFoD) {
                $arrStudents[] = [
          'id' => $stuApFoD->id,
          'eponimo' => $stuApFoD->eponimo,
          'onoma' => $stuApFoD->onoma,
          'patronimo' => $stuApFoD->patronimo,
          'tmima' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->first('tmima')->tmima,
          'tmimata' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->implode(', '),
          'apousies' => $stuApFoD->apousies[0]->where('student_id', $stuApFoD->id)->where('date', '<=', $eosDate)->orderby('date')->pluck('apousies', 'date')->toArray(),
        ];
            }
        } else {
            // βρίσκω τους μαθητές που έχουν απουσίες την συγκεκριμμένη ημέρα
            $students = Student::whereHas('apousies', function ($query) use ($nowDate) {
                $query->where('date', '=', $nowDate);
            })->orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata')->with('apousies')->get();

            foreach ($students as $stuApFoD) {
                $arrStudents[] = [
          'id' => $stuApFoD->id,
          'eponimo' => $stuApFoD->eponimo,
          'onoma' => $stuApFoD->onoma,
          'patronimo' => $stuApFoD->patronimo,
          'tmima' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->first('tmima')->tmima,
          'tmimata' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->implode(', '),
          'apousies' => $stuApFoD->apousies[0]->where('student_id', $stuApFoD->id)->where('date', $nowDate)->pluck('apousies', 'date')->toArray(),
        ];
            }
        }

        $newStudents = array();
        foreach ($arrStudents as $student) {
            foreach ($student['apousies'] as $date => $value) {
                $newStudents[] = [
      'id' => $student['id'],
      'eponimo' => $student['eponimo'],
      'onoma' => $student['onoma'],
      'patronimo' => $student['patronimo'],
      'tmima' => $student['tmima'],
      'tmimata' => $student['tmimata'],
      'date' => Carbon::createFromFormat("!Ymd", $date)->format("d/m/y"),
      'apousies' => $value
    ];
            }
        }


        usort($newStudents, function ($a, $b) {
            return $a['eponimo'] <=> $b['eponimo'] ?:
           $a['onoma'] <=> $b['onoma'] ?:
           strnatcasecmp($a['patronimo'], $b['patronimo']);
        });

        return view('apouxls', [
        'arrStudents' => $newStudents
      ]);
    }
}
