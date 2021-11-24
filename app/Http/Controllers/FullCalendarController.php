<?php

namespace App\Http\Controllers;

use App\User;
use Response;
use App\Event;
use App\Tmima;
use App\Config;
use App\Anathesi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\CalendarExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
// use Illuminate\Support\Facades\Log;


class FullCalendarController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('allow.calendar');
    }

    public function index()
    {

        $mathimata = $this->mathimata();

        return view('fullcalendar', compact('mathimata'));
    }


    public function data()
    {
        $isAdmin = Auth::user()->role->role == 'Διαχειριστής';

        $start = !empty($_GET["start"]) ? substr($_GET["start"], 0, 10) : '';
        $end = !empty($_GET["end"]) ? substr($_GET["end"], 0, 10) : '';
        $data = Event::where('start', '>=', $start)->where('end',   '<=', $end)->orderBy('start')->orderBy('title')->get();
        foreach ($data as $d) {
            $id = Auth::user()->id;
            if ($d->user_id == "$id" || $isAdmin) {
                $d->editable = true;
            }
        }
        return Response::json($data);
    }


    public function export()
    {
        $startLabel = request()->start ?? 'την_αρχή';
        $endLabel = request()->end ?? 'το_τέλος';
        return Excel::download(new CalendarExport(request()->start, request()->end), 'Διαγωνίσματα_από_' . $startLabel . '_έως_' . $endLabel . '.xls');
    }


    public function mathimata()
    {
        $isAdmin = Auth::user()->role->role == 'Διαχειριστής';

        $mathimata = Anathesi::select('mathima');
        if (!$isAdmin) {
            $mathimata = $mathimata->where('user_id', Auth::user()->id);
        }

        $mathimata = $mathimata->distinct()->orderBy('mathima')->pluck('mathima')->toArray();

        return $mathimata;
    }

    public function tmimata(Request $request)
    {
        // παίρνω τα δεδομένα
        $date = $request->start;
        $week = $request->week;
        $user_id = Auth::user()->id;
        $isAdmin = Auth::user()->role->role == 'Διαχειριστής';

        $maxDiagonismataForDay = Config::getConfigValueOf('maxDiagonismataForDay');
        $maxDiagonismataForWeek = Config::getConfigValueOf('maxDiagonismataForWeek');


        // βρίσκω ποια τμήματα έχουν διαγώνισμα σήμερα
        $tmimata = Event::where('start', $date)->select('tmima1', 'tmima2')->get();
        $withDiagonismaForDay = collect($tmimata->toArray())->all();
        // βρίσκω ποιοι μαθητές έχουν ήδη ένα προγραμματισμένο διαγώνισμα την ημέρα
        $studentsWithDiagonismataForDay = $this->studentsWithMaxDiagonismata($withDiagonismaForDay, $maxDiagonismataForDay);

        // βρίσκω ποια τμήματα έχουν διαγώνισμα την εβδομάδα
        $tmimata = Event::where('week', $week)->select('tmima1', 'tmima2')->get();
        $withDiagonismaForWeek = collect($tmimata->toArray())->all();
        // βρίσκω ποιοι μαθητές έχουν προγραμματισμένα συνολικά πάνω από τα επιτρεπόμενα διαγωνίσματα (3) την εβδομάδα
        $studentsWithMaxDiagonismataForWeek = $this->studentsWithMaxDiagonismata($withDiagonismaForWeek, $maxDiagonismataForWeek);

        // ποια τμήματα δεν χτυπάνε με τα προηγούμενα
        $tmimataNonConflict = $this->tmimataNotConflict($studentsWithDiagonismataForDay, $studentsWithMaxDiagonismataForWeek);

        // βρίσκω τις αναθέσεις για τον καθηγητή
        if ($isAdmin) {
            $anatheseis = $this->tmimataList();
        } else {
            $anatheseis = Anathesi::where('user_id', $user_id)->pluck('tmima')->toArray();
        }

        // ποια τμήματα είναι ελεύθερα για τον καθηγητή
        $tmimataNonConflictForTeacher = array_intersect($anatheseis, $tmimataNonConflict);

        // Log::channel('myinfo')->info($tmimataNonConflictForTeacher);

        return  collect($tmimataNonConflictForTeacher)->toJson();
    }




    public function create(Request $request)
    {

        $title = $request->tmima2  ?  $request->tmima1 . '-' . $request->tmima2  : $request->tmima1;
        $title .= ', ' . $request->mathima .  ', ' . User::find($request->user_id)->name;

        $event = Event::updateOrCreate([
            'user_id' => $request->user_id,
            'tmima1' => $request->tmima1,
            'tmima2' => $request->tmima2,
            'mathima' => $request->mathima
        ], [
            'title' => $title,
            'start' => $request->start,
            'end' => $request->end,
            'week' => $request->week,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        return Response::json($event);
    }


    public function update(Request $request)
    {

        // παίρνω τα δεδομένα
        $date = $request->start;
        $week = $request->week;
        $oldWeek = $request->oldWeek;
        $tmima1 = $request->tmima1;
        $tmima2 = $request->tmima2;
        $user_id = Auth::user()->id;

        // παίρνω τους μαθητές κάθε τμήματος σε πίνακα['τμήμα'] = μαθητές
        $studentsForTmima = $this->studentsForTmima();

        $maxDiagonismataForDay = Config::getConfigValueOf('maxDiagonismataForDay');
        $maxDiagonismataForWeek = Config::getConfigValueOf('maxDiagonismataForWeek');
        // Αν η αλλαγή διαγωνίσματος είναι μέσα στην ίδια εβδομάδα οπότε μετράει και το δικό μου διαγώνισμα
        // αυξάνω τον αριθμό για να μη χτυπάει και να επιτρέψει την αλλαγή
        if ($oldWeek == $week) $maxDiagonismataForWeek++;


        // βρίσκω ποια μαθητές έχουν διαγώνισμα σήμερα
        $tmimata = Event::where('start', $date)->select('tmima1', 'tmima2')->get();
        $withDiagonismaForDay = collect($tmimata->toArray())->all();
        // βρίσκω ποιοι μαθητές έχουν ήδη ένα προγραμματισμένο διαγώνισμα την ημέρα
        $studentsWithDiagonismataForDay = $this->studentsWithMaxDiagonismata($withDiagonismaForDay, $maxDiagonismataForDay);

        // βρίσκω ποια τμήματα έχουν διαγώνισμα την εβδομάδα
        $tmimata = Event::where('week', $week)->select('tmima1', 'tmima2')->get();
        $withDiagonismaForWeek = collect($tmimata->toArray())->all();
        // βρίσκω ποιοι μαθητές έχουν προγραμματισμένα συνολικά πάνω από τα επιτρεπόμενα διαγωνίσματα (3) την εβδομάδα
        $studentsWithMaxDiagonismataForWeek = $this->studentsWithMaxDiagonismata($withDiagonismaForWeek, $maxDiagonismataForWeek);

        if (count(array_intersect($studentsForTmima[$tmima1], $studentsWithDiagonismataForDay)) || ($tmima2 && count(array_intersect($studentsForTmima[$tmima2], $studentsWithDiagonismataForDay)))) {
            $message = 'Δεν μπορείτε να μεταθέσετε το διαγώνισμα στις ' . $request->start . ' γιατί ';
            if ($tmima2) {
                $message .= 'τουλάχιστον ένα από τα τμήματα ' . $tmima1 . '-' . $tmima2 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForDay . ' διαγωνίσματα στην ημέρα.';
            } else {
                $message .= 'τo τμήμα ' . $tmima1 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForDay . ' διαγωνίσματα στην ημέρα.';
            }
            return Response::json(['error' => $message], 409);
        }

        if (count(array_intersect($studentsForTmima[$tmima1], $studentsWithMaxDiagonismataForWeek)) || ($tmima2 && count(array_intersect($studentsForTmima[$tmima2], $studentsWithMaxDiagonismataForWeek)))) {
            $message = 'Δεν μπορείτε να μεταθέσετε το διαγώνισμα στις ' . $request->start . ' γιατί ';
            if ($tmima2) {
                $message .= 'τουλάχιστον ένα από τα τμήματα ' . $tmima1 . '-' . $tmima2 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForWeek . ' διαγωνίσματα στην εβδομάδα.';
            } else {
                $message .= 'τo τμήμα ' . $tmima1 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForWeek . ' διαγωνίσματα στην εβδομάδα.';
            }
            return Response::json(['error' => $message], 409);
        }

        $where = array('id' => $request->id);
        $updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $updateArr = [
            'start' => $request->start,
            'end' => $request->end,
            'week' => $request->week,
            'updated_at' => $updated_at,
        ];
        $event = Event::where($where)->first();
        $event->update($updateArr);

        return Response::json($event);
    }


    public function destroy(Request $request)
    {

        $event = Event::where('id', $request->id)->delete();

        return Response::json($event);
    }

    public function tmimataConflict()
    {
        // παίρνω τους μαθητές κάθε τμήματος σε πίνακα['τμήμα'] = μαθητές
        $studentsForTmima = $this->studentsForTmima();
        $arrConflicts = array();
        // συγκρίνω τους μαθητές κάθε τμήματος με όλα τα άλλα τμήματα
        foreach ($studentsForTmima as $tmima => $students) {
            foreach ($studentsForTmima as $tmi => $stu) {
                // αν υπάρχει τουλάχιστον ένας κοινός μαθητής τα τμήματα χτυπάνε
                // array_intersect => βρίσκει τις κοινές τιμές πινάκων
                if (count(array_intersect($students, $stu))) {
                    $arrConflicts[$tmima][] = $tmi;
                }
            }
        }
        return $arrConflicts;
    }

    public function tmimataList()
    {
        return Tmima::select('tmima')->distinct()->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->toArray();
    }

    public function tmimataNotConflict($withDiagonismaForDay = [], $withMaxDiagonismataForWeek = [])
    {
        // λίστα τμημάτων
        $tmimata = $this->tmimataList();
        // λίστα μαθητών κάθε τμήματος
        $studentsForTmima = $this->studentsForTmima();
        // ενώνω τους μαθητές που δεν πρέπει να γράψουν άλλο διαγώνισμα (για την ημέρα, για την εβδομάδα)
        $studentsMustNotWrite = array_unique(array_merge($withDiagonismaForDay, $withMaxDiagonismataForWeek));
        $tmimataNotConflict = array();

        foreach ($tmimata as $tmima) {
            // αν δεν υπάρχουν κοινοί μαθητές (μαθητές τμήματος, μαθητές που δεν πρέπει να γράψουν)
            // το τμήμα είναι ελέυθερο
            if (!count(array_intersect($studentsForTmima[$tmima], $studentsMustNotWrite))) {
                $tmimataNotConflict[] = $tmima;
            }
        }
        return $tmimataNotConflict;
    }

    public function events()
    {
        $isAdmin = Auth::user()->role->role == 'Διαχειριστής';
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $chkDate = Carbon::now()->format('Y-m-d');
        $totalStartMonthDay = Config::getConfigValueOf('totalStartMonthDay');
        $totalEndMonthDay = Config::getConfigValueOf('totalEndMonthDay');
        if ($month < 8) $year--;
        $start = $year . "-" . $totalStartMonthDay;
        $end = $year + 1 . "-" . $totalEndMonthDay;
        $events = Event::where('start', '>=', $start)->where('start', '<=', $end)->orderBy('start');
        if (!$isAdmin) {
            $events = $events->where('user_id', Auth::user()->id);
        }
        $events = $events->get();
        if (!count($events)) {
            return Response::json('Δεν έχετε καταχωρισμένα διαγωνίσματα');
        }

        $html = '<table  class ="table is-striped" style=" margin-left:auto;margin-right: auto;">
                <thead>
                <tr style="background-color: #f2f2f2;" >
                <th >Α/Α</th>
                <th >Ημ/νια</th>
                <th >Τμήμα</th>
                <th >Μάθημα</th>
                ';
        if ($isAdmin) {
            $html .= '<th>Καθηγητής</th>';
        }
        $html .= '<th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                ';
        $aa = 1;
        foreach ($events as $ev) {
            $class = '';
            if ($ev->start < $chkDate) {
                $class = 'class="has-text-grey-light"';
            }
            $tmima = $ev->tmima2 ?  "$ev->tmima1 - $ev->tmima2" :   $ev->tmima1;
            $html .= "<tr $class><td class='has-text-centered'>$aa</td><td>$ev->start</td><td class='has-text-centered'>$tmima</td><td>$ev->mathima</td>";
            if ($isAdmin) {
                $name = User::where('id', $ev->user_id)->select('name')->first()->name;
                $html .= "<td>$name</td>";
            }
            $html .= "<td><a class=\"button\" href=\"javascript:triggerDelete($ev->id, '$ev->title', '$ev->start')\" id=\"del$ev->id\" title=\"Διαγραφή $ev->start, $ev->title\">
                            <span class=\"icon\"><i class=\"fa fa-trash\"></i></span>
                            </a></td>";
            $html .= "</tr>";
            $aa++;
        }
        $html .= '</tbody>
                </table>';

        return Response::json($html);
    }

    public function studentsForTmima()
    {
        // παίρνω από τα τμήματα το τμήμα και το student_id
        $stuForTmima = Tmima::orderByRaw('LENGTH(tmima)')->orderBy('tmima')->get(['tmima', 'student_id'])->toArray();
        $studentsForTmima = array();
        // προσθέτω για κάθε τμήμα πίνακα με τα sudent_id των μαθητών
        foreach ($stuForTmima  as $stu) {
            $studentsForTmima[$stu['tmima']][] = $stu['student_id'];
        }
        return $studentsForTmima;
    }

    public function studentsWithMaxDiagonismata($withDiagonisma, $maxNum)
    {

        $studentsWithMaxDiagonismata = [];
        // παίρνω τους μαθητές κάθε τμήματος
        $studentsForTmima = $this->studentsForTmima();

        $totalConflicts = array();
        // για κάθε τμήμα με διαγώνισμα
        foreach ($withDiagonisma as $withDia) {
            // το 1ο τμήμα απαιτείται. Προσθέτω τους μαθητές
            $conflicts = $studentsForTmima[$withDia['tmima1']];
            // αν έχει επιλεγεί και 2ο τμήμα
            if ($withDia['tmima2']) {
                // προσθέτω τους μαθητές. Παίρνω πίνακα με μοναδικές τιμές μαθητών για κάθε ζευγάρι τμημάτων
                $conflicts = array_unique(array_merge($conflicts, $studentsForTmima[$withDia['tmima2']]));
            }
            // προστίθενται όλοι οι μαθητές όσες φορές έγραψαν διαγώνισμα
            $totalConflicts = array_merge($totalConflicts, $conflicts);
        }
        // πόσες φορές εμφανίζεται (γράφει) κάθε μαθητής
        $sixnotitaOfStudents = array_count_values($totalConflicts);
        arsort($sixnotitaOfStudents);
        foreach ($sixnotitaOfStudents as $key => $value) {
            // Αν εμφανίζεται >= με $maxNum μπαίνει στον πίνακα ως μη διαθέσιμος μαθητής
            if ($value > $maxNum - 1) {
                $studentsWithMaxDiagonismata[] = $key;
            }
        }
        return $studentsWithMaxDiagonismata;
    }
}
