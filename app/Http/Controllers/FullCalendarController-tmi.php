<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Response;
use App\Event;
use App\Student;
use App\Anathesi;
use App\Config;
use App\User;
use App\Tmima;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;


class FullCalendarController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $mathimata = $this->mathimata();

        return view('fullcalendar', compact('mathimata'));
    }

    public function data()
    {
        $isAdmin = Auth::user()->role_description() == 'Διαχειριστής';

        $start = !empty($_GET["start"]) ? $_GET["start"] : '';
        $end = !empty($_GET["end"]) ? $_GET["end"] : '';
        $data = Event::where('start', '>=', $start)->where('end',   '<=', $end)->orderBy('title')->get();
        foreach ($data as $d) {
            $id = Auth::user()->id;
            if ($d->user_id == "$id" || $isAdmin) {
                $d->editable = true;
            }
        }
        return Response::json($data);
    }

    public function mathimata()
    {
        $isAdmin = Auth::user()->role_description() == 'Διαχειριστής';

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
        $isAdmin = Auth::user()->role_description() == 'Διαχειριστής';

        $maxDiagonismataForDay = Config::getConfigValueOf('maxDiagonismataForDay');
        $maxDiagonismataForWeek = Config::getConfigValueOf('maxDiagonismataForWeek');


        // βρίσκω ποια τμήματα έχουν διαγώνισμα σήμερα
        $tmimata = Event::where('start', $date)->select('tmima1', 'tmima2')->get();
        $withDiagonismaForDay = collect($tmimata->toArray())->all();
        $tmimataWithDiagonismataForDay = $this->tmimataWithMaxDiagonismata($withDiagonismaForDay, $maxDiagonismataForDay);

        // βρίσκω ποια τμήματα έχουν διαγώνισμα την εβδομάδα
        $tmimata = Event::where('week', $week)->select('tmima1', 'tmima2')->get();
        $withDiagonismaForWeek = collect($tmimata->toArray())->all();
        // βρίσκω ποια τμήματα έχουν συνολικά πάνω από τα επιτρεπόμενα διαγωνίσματα (3) την εβδομάδα
        $tmimataWithMaxDiagonismataForWeek = $this->tmimataWithMaxDiagonismata($withDiagonismaForWeek, $maxDiagonismataForWeek);

        // για Debug
        // Log::channel('myinfo')->info(array_unique(array_merge($tmimataWithDiagonismataForDay, $tmimataWithMaxDiagonismataForWeek)));

        // ποια τμήματα δεν χτυπάνε με τα προηγούμενα
        $tmimataNonConflict = $this->tmimataNotConflict($tmimataWithDiagonismataForDay, $tmimataWithMaxDiagonismataForWeek);

        // βρίσκω τις αναθέσεις για τον καθηγητή
        if ($isAdmin) {
            $anatheseis = $this->tmimataList();
        } else {
            $anatheseis = Anathesi::where('user_id', $user_id)->pluck('tmima')->toArray();
        }

        // ποια τμήματα είναι ελεύθερα για τον καθηγητή
        $tmimataNonConflictForTeacher = array_intersect($anatheseis, $tmimataNonConflict);

        Log::channel('myinfo')->info($tmimataNonConflictForTeacher);

        return  collect($tmimataNonConflictForTeacher)->toJson();
    }




    public function create(Request $request)
    {

        $title = $request->tmima2  ?  $request->tmima1 . '-' . $request->tmima2  : $request->tmima1;
        $title .= ', ' . $request->mathima .  ', ' . User::find($request->user_id)->name;

        $insertArr = [
            'title' => $title,
            'start' => $request->start,
            'end' => $request->end,
            'week' => $request->week,
            'tmima1' => $request->tmima1,
            'mathima' => $request->mathima,
            'tmima2' => $request->tmima2,
            'user_id' => $request->user_id,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
        $event = Event::create($insertArr);
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

        $maxDiagonismataForDay = Config::getConfigValueOf('maxDiagonismataForDay');
        $maxDiagonismataForWeek = Config::getConfigValueOf('maxDiagonismataForWeek');
        // Αν η αλλαγή διαγωνίσματος είναι μέσα στην ίδια εβδομάδα
        // αυξάνω τον αριθμό για να μη χτυπάει και να επιτρέψει την αλλαγή
        if ($oldWeek == $week) $maxDiagonismataForWeek++;


        // βρίσκω ποια τμήματα έχουν διαγώνισμα σήμερα
        $tmimata = Event::where('start', $date)->select('tmima1', 'tmima2')->get();
        $withDiagonismaForDay = collect($tmimata->toArray())->all();
        $tmimataWithDiagonismataForDay = $this->tmimataWithMaxDiagonismata($withDiagonismaForDay, $maxDiagonismataForDay);

        // βρίσκω ποια τμήματα έχουν διαγώνισμα την εβδομάδα
        $tmimata = Event::where('week', $week)->select('tmima1', 'tmima2')->get();
        $withDiagonismaForWeek = collect($tmimata->toArray())->all();
        // βρίσκω ποια τμήματα έχουν συνολικά πάνω από τα επιτρεπόμενα διαγωνίσματα (3) την εβδομάδα
        $tmimataWithMaxDiagonismataForWeek = $this->tmimataWithMaxDiagonismata($withDiagonismaForWeek, $maxDiagonismataForWeek);

        if (in_array($tmima1, $tmimataWithDiagonismataForDay) || ($tmima2 && in_array($tmima2, $tmimataWithDiagonismataForDay))) {
            $message = 'Δεν μπορείτε να μεταθέσετε το διαγώνισμα στις ' . $request->start . ' γιατί ';
            if ($tmima2) {
                if ($maxDiagonismataForDay > 1) {
                    $message .= 'τουλάχιστον ένα από τα τμήματα ' . $tmima1 . '-' . $tmima2 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForDay . ' διαγωνίσματα στην ημέρα.';
                } else {
                    $message .= 'τουλάχιστον ένα από τα τμήματα ' . $tmima1 . '-' . $tmima2 . ' έχει ήδη προγραμματισμένο διαγώνισμα για την συγκεκριμένη ημέρα.';
                }
            } else {
                if ($maxDiagonismataForDay > 1) {
                    $message .= 'τo τμήμα ' . $tmima1 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForDay . ' διαγωνίσματα στην ημέρα.';
                } else {
                    $message .= 'τo τμήμα ' . $tmima1 . ' έχει ήδη προγραμματισμένο διαγώνισμα για την συγκεκριμένη ημέρα.';
                }
            }
            return Response::json(['error' => $message], 409);
        }

        if (in_array($tmima1, $tmimataWithMaxDiagonismataForWeek) || ($tmima2 && in_array($tmima2, $tmimataWithMaxDiagonismataForWeek))) {
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

    public function tmimataWithMaxDiagonismata($withDiagonisma, $maxNum)
    {

        $tmimataWithMaxDiagonismata = [];

        // παίρνω τον πίνακα με τα σχετικά τμήματα
        $arrConflicts = $this->tmimataConflict();
        $totalConflicts = array();
        // για κάθε τμήμα με διαγώνισμα
        foreach ($withDiagonisma as $withDia) {
            // το 1ο τμήμα απαιτείται. Προσθέτω τα σχετικά τμήματα
            $conflicts = $arrConflicts[$withDia['tmima1']];
            // αν έχει επιλεγεί και 2ο τμήμα
            if ($withDia['tmima2']) {
                // προσθέτω τα σχετικά τμήματα. Παίρνω πίνακα με μοναδικές τιμές τμήματος για κάθε ζευγάρι
                $conflicts = array_unique(array_merge($conflicts, $arrConflicts[$withDia['tmima2']]));
            }
            // πίνακας τα σχετικά τμήματα για όλα τα τμήματα
            $totalConflicts = array_merge($totalConflicts, $conflicts);
        }
        //Log::channel('myinfo')->info($totalConflicts); 
        // πόσες φορές εμφανίζεται κάθε τμήμα
        $sixnotitaOfTmimata = array_count_values($totalConflicts);
        arsort($sixnotitaOfTmimata);
        //Log::channel('myinfo')->info($sixnotitaOfTmimata); 
        foreach ($sixnotitaOfTmimata as $key => $value) {
            // Αν εμφανίζεται >= με $maxNum μπαίνει στον πίνακα ως μη διαθέσιμο
            if ($value > $maxNum - 1) {
                $tmimataWithMaxDiagonismata[] = $key;
            }
        }
        return $tmimataWithMaxDiagonismata;
    }

    public function tmimataConflict()
    {
        $students = Student::with("tmimata")->get();
        $arrConflicts = array();
        foreach ($students as $stu) {
            // βάζω όλα τα τμήματα του μαθητή σε ένα πίνακα
            $conflicts = array();
            foreach ($stu->tmimata as $tmi) {
                $conflicts[] = $tmi->tmima;
            }
            // για κάθε τμήμα του μαθητή ($key)
            // προσθέτω όλα τα τμήματα και παίρνω τα μοναδικά
            foreach ($conflicts as $tmi) {
                if (!is_array($arrConflicts[$tmi])) $arrConflicts[$tmi] = [];
                $arrConflicts[$tmi] = array_unique(array_merge($arrConflicts[$tmi], $conflicts));
            }
        }
        return $arrConflicts;
    }

    public function tmimataList()
    {
        return Tmima::select('tmima')->distinct()->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->toArray();
    }

    public function tmimataNotConflict($tmimataWithDiagonismaForDay = [], $tmimataWithMaxDiagonismataForWeek = [])
    {
        $tmimata = $this->tmimataList();

        // βγάζω έξω από τη λίστα των τμημάτων όσα τμήματα χτυπάνε για την ημέρα
        $tmimata =  array_values(array_diff($tmimata, $tmimataWithDiagonismaForDay));
        // βγάζω έξω από τη λίστα των τμημάτων όσα τμήματα χτυπάνε για την εβδομάδα
        $tmimata =  array_values(array_diff($tmimata, $tmimataWithMaxDiagonismataForWeek));

        return $tmimata;
    }

    public function events()
    {
        $isAdmin = Auth::user()->role_description() == 'Διαχειριστής';
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
}
