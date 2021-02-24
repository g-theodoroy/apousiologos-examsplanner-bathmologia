<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Imports\StudentsImport;
use App\Imports\ProgramImport;
use App\Imports\ApousiesMyschoolImport;
use App\Exports\ApousiesForDayExport;
use App\Exports\KathigitesExport;
use App\Exports\MathitesExport;
use App\Exports\ProgramExport;
use App\Exports\ApousiesMyschoolExport;
use App\User;
use App\Student;
use App\Role;
use App\Tmima;
use App\Program;
use App\Apousie;
use App\Anathesi;
use App\Grade;
use App\Config;
use Carbon\Carbon;

class AdminController extends Controller
{
    //

    public function __construct()
  {
  $this->middleware('auth');
  $this->middleware('web');
  $this->middleware('admin');
  }

  public function index()
  {
    $user = new User;
    $kathigitesCount = $user->get_num_of_kathigites();
    $student = new Student;
    $studentsCount = $student->get_num_of_students();
    $program = new Program;
    $programCount = $program->get_num_of_hours();
      return view('admin', compact('kathigitesCount', 'studentsCount', 'programCount' ));
  }

  public function insertUsers()
  {
    $import = new UsersImport;
    Excel::import($import, request()->file('file_kath'));
    $insertedUsersCount = $import->getUsersCount();
    $insertedAnatheseisCount = $import->getAnatheseisCount();
    return redirect()->route('admin')->with( ['insertedUsersCount' => $insertedUsersCount,'insertedAnatheseisCount' => $insertedAnatheseisCount] );
  }

  public function insertStudents()
  {
    $import = new StudentsImport;
    Excel::import($import, request()->file('file_math'));
    $insertedStudentsCount = $import->getStudentsCount();
    $insertedTmimataCount = $import->getTmimataCount();
    return redirect()->route('admin')->with( ['insertedStudentsCount' => $insertedStudentsCount,'insertedTmimataCount' => $insertedTmimataCount] );
  }

  public function insertProgram()
  {
    $import = new ProgramImport;
    Program::truncate();
    Excel::import($import, request()->file('file_prog'));
    return redirect()->route('admin')->with( ['insertedProgram' => 1] );
  }

  public function insertMyschoolApousies()
  {
    $import = new ApousiesMyschoolImport;
    Excel::import($import, request()->file('file_myschAp'));
    $insertedStudentsApousiesCount = $import->getStudentsApousiesCount();
    $insertedDaysApousiesCount = $import->getDaysApousiesCount();
    return redirect()->route('admin')->with( ['insertedStudentsApousiesCount' => $insertedStudentsApousiesCount,'insertedDaysApousiesCount' => $insertedDaysApousiesCount]);
  }


  public function setConfigs()
  {
    $val = request()->has('allowRegister') ? 1 : null;
    Config::setConfigValueOf('allowRegister',$val);
    $val = request()->has('hoursUnlocked') ? 1 : null;
    Config::setConfigValueOf('hoursUnlocked',$val);
    $val = request()->has('allowTeachersSaveAtNotActiveHour') ? 1 : null;
    Config::setConfigValueOf('allowTeachersSaveAtNotActiveHour',$val);
    $val = request()->has('letTeachersUnlockHours') ? 1 : null;
    Config::setConfigValueOf('letTeachersUnlockHours',$val);
    $val = request()->has('showFutureHours') ? 1 : null;
    Config::setConfigValueOf('showFutureHours',$val);
    $val = request()->has('allowWeekends') ? 1 : null;
    Config::setConfigValueOf('allowWeekends',$val);
    Config::setConfigValueOf('schoolName',request()->input('schoolName'));
    Config::setConfigValueOf('setCustomDate',request()->input('setCustomDate'));
    Config::setConfigValueOf('timeZone', request()->input('timeZone'));
    Config::setConfigValueOf('maxDiagonismataForDay', request()->input('maxDiagonismataForDay'));
    Config::setConfigValueOf('maxDiagonismataForWeek', request()->input('maxDiagonismataForWeek'));
    Config::setConfigValueOf('totalStartMonthDay', request()->input('totalStartMonthDay'));
    Config::setConfigValueOf('totalEndMonthDay', request()->input('totalEndMonthDay'));
    Config::setConfigValueOf('activeGradePeriod', request()->input('activeGradePeriod'));
    Config::setConfigValueOf('showOtherGrades', request()->input('showOtherGrades'));
    Config::setConfigValueOf('pastDaysInsertApousies', request()->input('pastDaysInsertApousies'));
    return redirect()->route('admin')->with( ['setDone' => 1] );
  }

  public function delKathigites()
  {
    $delKathigitesCount = User::whereRoleId(Role::whereRole('Καθηγητής')->first()->id)->count();
    $users = User::whereRoleId(Role::whereRole('Καθηγητής')->first()->id)->get();
    foreach ($users as $user) $user->anatheseis()->delete();
    User::whereRoleId(Role::whereRole('Καθηγητής')->first()->id)->delete();
    return redirect()->route('admin')->with( ['delKathigitesCount' => $delKathigitesCount ] );
  }

  public function delStudents()
  {
    $delStudentsCount = Student::all()->count();
    Student::truncate();
    Tmima::truncate();
    return redirect()->route('admin')->with( ['delStudentsCount' => $delStudentsCount ] );
  }

  public function delProgram()
  {
    Program::truncate();
    return redirect()->route('admin')->with( ['deletedProgram' => 1 ] );
  }
  public function delApousies($keep = null)
  {
    if (! $keep){
      Apousie::truncate();
    } else {
      Apousie::where('date', '<', Carbon::now()->format("Ymd")-$keep)->delete();
    }
    return redirect()->route('admin')->with( ['deletedApousies' => 1 ] );
  }

  public function export()
  {
    return view('export');
  }

  public function exportApousiesXls()
  {
    $apoDate = request()->apoDate;
    $eosDate = request()->eosDate;
    if( $apoDate && $eosDate){
      if ($apoDate == $eosDate){
        $filenameDates = '_για_τις_' . Carbon::createFromFormat("!d/m/y", $apoDate)->format("Ymd");
      }else{
        $filenameDates = '_από_' . Carbon::createFromFormat("!d/m/y", $apoDate)->format("Ymd") . '_έως_' . Carbon::createFromFormat("!d/m/y", $eosDate)->format("Ymd");
      }
    }elseif(! $apoDate && $eosDate){
      $filenameDates = '_έως_τις_' . Carbon::createFromFormat("!d/m/y", $eosDate)->format("Ymd");
    }elseif( $apoDate && ! $eosDate){
      $filenameDates = '_από_τις_' . Carbon::createFromFormat("!d/m/y", $apoDate)->format("Ymd");
    }else{
      $filenameDates = '_για_τις_' . Carbon::now()->format("Ymd");
    }
    return Excel::download(new ApousiesForDayExport($apoDate, $eosDate), 'myschool_Eisagwgh_Apousiwn_Mazika_apo_Excel_by_GΘ' . $filenameDates .'.xls');
 }

public function exportKathigitesXls()
 {
   return Excel::download(new KathigitesExport, 'Καθηγητές_και_Αναθέσεις_by_GΘ.xls');
}

public function exportMathitesXls()
{
  return Excel::download(new MathitesExport, 'Μαθητές_και_Τμήματα_by_GΘ.xls');
}

public function exportProgramXls()
{
  return Excel::download(new ProgramExport, 'Ωρολόγιο_Πρόγραμμα_by_GΘ.xls');
}
public function exportApousiesMyschoolXls()
{
  return Excel::download(new ApousiesMyschoolExport, 'Πρότυπο για εισαγωγή απουσιών από Myschool_by_GΘ.xls');
}

public function populateXls( $insertToDB = 0)
{
  $lessonsRow = request()->input('labelsRow');
  $startRow = $lessonsRow + 1;
  $startCol = request()->input('firstLessonCol');
  $amCol = request()->input('amCol');
  Config::setConfigValueOf('187XlsLabelsRow', $lessonsRow);
  Config::setConfigValueOf('187XlsAmCol', $amCol);
  Config::setConfigValueOf('187XlsFirstLessonCol', $startCol);

  // ανοίγω το xls
  $file = request()->file('file_xls');
  $filename = request()->file('file_xls')->getClientOriginalName();
  $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
  $spreadsheet = $reader->load($file);

  $sheet = $spreadsheet->getActiveSheet();
  $maxCol = $sheet->getHighestColumn();
  $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxCol);
  $maxRow = $sheet->getHighestRow();
  
  // βάζω σε πίνακα[μάθημα] = στήλη τις στήλες των μαθημάτων[]
  $lessons = array();
  for($col = $startCol; $col< $maxColIndex; $col++) {
      $lessons[$sheet->getCellByColumnAndRow($col, $lessonsRow)->getValue()] = $col;
  }
  // βάζω σε πίνακα[αμ] = γραμμή τις γραμμές των αρ μητρώου
  $amArr = array();
  for ($row = $startRow; $row < $maxRow + 1 ; $row++) {
      $amArr[$sheet->getCellByColumnAndRow($amCol, $row)->getValue()] = $row;
  }


// αν θέλω να γεμίσω τη ΒΔ
if($insertToDB){
    // πίνακας[αμ] = [τμη1, τμη2, τμη3] με τμήματα κάθε μαθητή
    $tmimata =  Tmima::get(['student_id','tmima']);
    $arrTmimata = array();
    foreach($tmimata as $tmi){
          $arrTmimata[$tmi->student_id][] = $tmi->tmima;
    }
    // πίνακας[μάθημα][τμημα] = id ανάθεσης
    // για να βρώ τον κωδικό ανάθεσης
    $anatheseis = Anathesi::get(['id','mathima', 'tmima']);
    $arrAnatheseis = array();
    foreach($anatheseis as $anath) {
      $arrAnatheseis[$anath->mathima][$anath->tmima] = $anath->id;
    }

    $insertedGradesCount = 0;
    // για κάθε am
    foreach ($amArr as $am => $row) {
      // για κάθε μάθημα
      foreach ($lessons as $lesson => $col) {
        // παίρνω το βαθμό από το κελί
        $grade = $sheet->getCellByColumnAndRow($col, $row)->getValue();

        
        // βρίσκω το κοινό τμήμα μαθητή και μαθήματος και με αυτό βρίσκω το id της ανάθεσης
        $tmima = array_values(array_intersect($arrTmimata[$am], array_keys($arrAnatheseis[$lesson]) ));
        $anathesi_id = $arrAnatheseis[$lesson][$tmima[0]];
        //echo $am . " - " . $lesson  . " - " . $tmima[0]  . " - " . $anathesi_id  . " - " . $grade . "<hr>";
        
        // αν υπάρχει id
        if($anathesi_id){
            if ($grade) {
              // ενημερώνω ή εισάγω
              Grade::updateOrCreate(['anathesi_id' => $anathesi_id, 'student_id' =>  $am, 'period_id' => Config::getConfigValueOf('activeGradePeriod')], [
                'grade' => str_replace(".", ",", $grade),
              ]);
            } else {
              // αν δεν υπάρχει βαθμός διαγράφω
              Grade::where('anathesi_id', $anathesi_id)->where('student_id', $am)->where('period_id', Config::getConfigValueOf('activeGradePeriod'))->delete();
            }
            $insertedGradesCount++;
        }

      }
    }
    // επιστρέφω στη σελίδα
    return redirect()->route('admin')->with(['insertedGradesCount' => $insertedGradesCount]);
}

    // αλλιώς θα ενημερώσω το xls και θα το γυρίσω πίσω
    // παίρνω τους βαθμούς για την ενεργή περίοδο
    $grades = Grade::where('period_id', Config::getConfigValueOf('activeGradePeriod'))->get(['anathesi_id', 'student_id', 'grade']);
    // φτιάχνω πίνακα[μάθημα][αμ]=βαθμός 
    $finalGrades = array();
    foreach ($grades as $grade) {
      $finalGrades[Anathesi::find($grade->anathesi_id)->mathima][$grade->student_id] = $grade->grade;
    }

  // γεμίζω το xls
  foreach($amArr as $am=>$row){
    foreach($lessons as $lesson=>$col){
        $sheet->getCellByColumnAndRow($col, $row)->setValue($finalGrades[$lesson][$am]);
    }
  }

  // το στέλνω για download στο χρήστη
  $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);

  header('Content-type: application/ms-excel');
  header('Content-Disposition: attachment; filename=' . urlencode($filename));
  $writer->save('php://output');

  return;
}


}
