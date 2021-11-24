<?php

namespace App\Exports;

use App\User;
use App\Event;
use App\Program;
use Carbon\Carbon;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CalendarExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{

    private $start;
    private $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:E1')->getFont()->setSize(12)->setBold(true);
                $event->sheet->getDelegate()->getStyle('A1:E1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
                $event->sheet->getDefaultRowDimension()->setRowHeight(20);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'ΗΜΝΙΑ',
            'ΜΑΘΗΜΑ',
            'ΤΜΗΜΑ1',
            'ΤΜΗΜΑ2',
            'ΕΚΠΑΙΔΕΥΤΙΚΟΣ'
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $events = Event::orderBy('start')->orderBy('title');
        if ($this->start) $events = $events->where('start', '>=', $this->start);
        if ($this->end) $events = $events->where('end',   '<=', $this->end);
        $events = $events->get(['start', 'mathima',  'tmima1', 'tmima2', 'user_id']);
        foreach ($events as $event) {
            $event->user_id = User::find($event->user_id)->name;
            $event->start = Carbon::parse($event->start)->format('d/m/Y');
        }
        return $events;
    }
}
