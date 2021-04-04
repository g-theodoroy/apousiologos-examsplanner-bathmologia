<?php

namespace App\Exports;

use App\Config;
use App\Student;
use App\Anathesi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class BathmologiaExport implements FromCollection, WithCustomStartCell, WithEvents
{

    public function startCell(): string
    {
        return 'A3';
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $width = 3;
                $fontsize = 10;
                $maxCol = $event->sheet->getDelegate()->getHighestColumn();
                $maxRow = $event->sheet->getDelegate()->getHighestRow();

                $event->sheet->getDelegate()->getStyle('A3:' . $maxCol . '3')->getFont()->setSize($fontsize)->setBold(true);

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth($width);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth($width * 2);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth($width * 6);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth($width * 6);
                for ($i = 'E'; $i !== $maxCol; $i++) {
                    $event->sheet->getDelegate()->getColumnDimension($i)->setWidth($width * 1.2);
                }
                $event->sheet->getDelegate()->getColumnDimension($maxCol)->setWidth($width * 1.2);

                $event->sheet->getDelegate()->getStyle('A4:' . $maxCol . $maxRow)->getFont()->setSize($fontsize);
                $event->sheet->getDelegate()->getStyle('A3:' . $maxCol . $maxRow)->getBorders()->getAllBorders()->setBorderStyle('thin');
            },
        ];
    }


    public function collection()
    {
        $students = Student::orderBy('eponimo')->orderBy('onoma')->with('tmimata')->get();
        $mathimata = Anathesi::select('mathima')->distinct()->orderBy('mathima')->pluck('mathima')->toArray();

        // φτιάχνω πίνακα με τους μαθητές
        $arrStudents = array();
        $num = 0;
        foreach($students as $student){
            // παίρνω τους βαθμούς για την ενεργή βαθμολογική περίοδο
            $gradesPeriodLessons = array();
            foreach ($student->anatheseis as $anath) {
                if($anath->pivot->period_id == Config::getConfigValueOf('activeGradePeriod')){
                    $gradesPeriodLessons[$anath->mathima] = $anath->pivot->grade;
                }
            }
            // βρίσκω την τάξη παίρνοντας το πρώτο γράμμα του μικρότερου σε μήκος τμήματος ( Α1, Β1, Γ1)
            $arrStudents[$num]['taxi'] = mb_substr($student->tmimata[0]->where('student_id', $student->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->first('tmima')->tmima, 0, 1);
            $arrStudents[$num]['am'] = $student->id;
            $arrStudents[$num]['eponimo'] = $student->eponimo;
            $arrStudents[$num]['onoma'] = $student->onoma;
            // προσθέτω τους βαθμούς ανά μάθημα
            foreach($mathimata as $mathima){
                $arrStudents[$num][$mathima] =  $gradesPeriodLessons[$mathima] ?? null;
            }
            $num++;
        }
        // ταξινόμηση κατά τάξη - επώνυμο - όνομα
        usort($arrStudents, function ($a, $b) {
            return $a['taxi'] <=> $b['taxi'] ?:
            $a['eponimo'] <=> $b['eponimo'] ?:
            strnatcasecmp($a['onoma'], $b['onoma']);
        });

        // ετοιμάζω τον τελικό πίνακα μαθητών για το xls
        $num = 0;
        $arrStudentsFinal = array();

        // γραμμή επικεφαλίδων
        $arrStudentsFinal[$num][] = 'Α/Α';
        $arrStudentsFinal[$num][] = 'Αριθμός μητρώου';
        $arrStudentsFinal[$num][] = 'Επώνυμο μαθητή';
        $arrStudentsFinal[$num][] = 'Όνομα μαθητή';
        // μαθήματα γραμμής επικεφαλίδων 
        foreach ($mathimata as $mathima) {
            $arrStudentsFinal[$num][] =  $mathima;
        }
        $num++;
        // προσθέτω τα στοιχεία μαθητών και βαθμούς
        $taxi = '';
        $showNum = 1;
        foreach($arrStudents as $student){
            // με την αλλαγή τάξης ξαναξεκινάω τον Α/Α από το 1
            if($taxi != $student['taxi']) $showNum = 1;
            $arrStudentsFinal[$num][] = $showNum;
            $arrStudentsFinal[$num][] = $student['am'];
            $arrStudentsFinal[$num][] = $student['eponimo'];
            $arrStudentsFinal[$num][] = $student['onoma'];
            // βαθμοί
            foreach ($mathimata as $mathima) {
                $arrStudentsFinal[$num][] =  $student[$mathima];
            }
            $num++;
            $showNum++;
            $taxi = $student['taxi'];
        }

        return collect($arrStudentsFinal);
    }
}
