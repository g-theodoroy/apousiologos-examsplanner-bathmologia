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

}
