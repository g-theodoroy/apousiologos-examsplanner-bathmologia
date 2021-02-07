<?php

namespace App\Http\Controllers;

use App\Anathesi;
use App\Grade;
use App\Config;
use App\Exports\BathmologiaExport;
use App\Period;
use App\Student;
use App\Tmima;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class GradeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('allow.grades');
    }

    public function index($selectedAnathesiId = 0)
    {
        // Αν έχει υποβληθεί η φόρμα
        if (request()->method() == 'POST') {

            $data = request()->except(['_token']);
            foreach ($data as $am=>$grade){
                if ($grade){
                    Grade::updateOrCreate(['anathesi_id' => $selectedAnathesiId, 'student_id' =>  ltrim($am, 'b'),'period_id' => Config::getConfigValueOf('activeGradePeriod') ], [
                        'grade' => str_replace(".",",",$grade),
                    ]);
                }else{
                    Grade::where('anathesi_id', $selectedAnathesiId )->where('student_id', ltrim($am, 'b'))->where('period_id', Config::getConfigValueOf('activeGradePeriod') )->delete();
                }
             }


        } // τέλος Αν έχει υποβληθεί η φόρμα


         if ($selectedAnathesiId){
            $selectedAnathesi = Anathesi::find($selectedAnathesiId);
            $selectedTmima = $selectedAnathesi->tmima;
            $selectedMathima = $selectedAnathesi->mathima;
            $grades = Grade::where('anathesi_id', $selectedAnathesiId)->where('period_id', Config::getConfigValueOf('activeGradePeriod'))->pluck('grade', 'student_id');
            $allGrades = Grade::where('anathesi_id', $selectedAnathesiId)->get(['grade', 'student_id', 'period_id'])->toArray();
            $gradesStudentsPeriod = array();
            foreach($allGrades as $gr){
                $gradesStudentsPeriod[$gr['student_id']][$gr['period_id']]= $gr['grade'];
            }
        }

        //print_r($gradesStudentsPeriod);

        // παίρνω τα τμηματα του χρήστη
        // ταξινόμηση με το μήκος του ονόματος + αλφαβητικά
        $anatheseis = Auth::user()->anatheseis()->orderby('mathima')->orderByRaw('LENGTH(tmima)')->orderby('tmima')->get();

        // αν είναι Διαχειριστής τα παίρνω όλα από μια φορά
        if (Auth::user()->role_description() == "Διαχειριστής") {
            $anatheseis = Anathesi::orderby('mathima')->orderByRaw('LENGTH(tmima)')->orderby('tmima')->get();
        }

        // αν το τμήμα που δόθηκε στο url δεν αντιστοιχεί στον χρήστη επιστρέφω πίσω
        if ($selectedAnathesiId && !$anatheseis->where('id', $selectedAnathesiId)->count()) return back();

        $students = array();

        if ($selectedTmima) {
            // βάζω σε ένα πίνακα τους ΑΜ των μαθητών που ανήκουν στο επιλεγμένο τμήμα
            $student_ids = Tmima::where('tmima', $selectedTmima)->pluck('student_id')->toArray();

            // παίρνω τα στοιχεία των μαθητών ταξινομημένα κσι φιλτράρω μόνο τους ΑΜ που έχει το τμήμα
            $students = Student::orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata')->get()->only($student_ids);
        }

        $arrStudents = array();
        $gradesPeriodLessons = array();
        foreach ($students as $stuApFoD) {
            foreach($stuApFoD->anatheseis as $anath){
                $gradesPeriodLessons[$stuApFoD->id]['name'] = $stuApFoD->eponimo . ' ' . $stuApFoD->onoma;
                $gradesPeriodLessons[$stuApFoD->id][$anath->mathima][$anath->pivot->period_id] = $anath->pivot->grade;
            }
            $arrStudents[] = [
                'id' => $stuApFoD->id,
                'eponimo' => $stuApFoD->eponimo,
                'onoma' => $stuApFoD->onoma,
                'patronimo' => $stuApFoD->patronimo,
                'tmima' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->first('tmima')->tmima,
                'tmimata' => $stuApFoD->tmimata[0]->where('student_id', $stuApFoD->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->implode(', '),
                'grade' => $grades[$stuApFoD->id]
            ];
        }


        usort($arrStudents, function ($a, $b) {
            return $a['tmima'] <=> $b['tmima'] ?:
                $a['eponimo'] <=> $b['eponimo'] ?:
                $a['onoma'] <=> $b['onoma'] ?:
                strnatcasecmp($a['patronimo'], $b['patronimo']);
        });

        $mathimata = Anathesi::select('mathima')->distinct()->orderBy('mathima')->pluck('mathima')->toArray();


        return view('grades', compact('anatheseis','selectedAnathesiId', 'selectedTmima', 'selectedMathima', 'arrStudents', 'gradesStudentsPeriod',  'gradesPeriodLessons', 'mathimata'));
    }

    public function exportGradesXls(){
        $filename = Config::getConfigValueOf('schoolName') . ' - ' . Period::find(Config::getConfigValueOf('activeGradePeriod'))->period . ' - 187.xls';
        return Excel::download(new BathmologiaExport, $filename);
    }

}
