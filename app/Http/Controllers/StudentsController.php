<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Student;
use App\Tmima;
use App\Apousie;
use App\Program;
use DataTables;
use Carbon\Carbon;

class StudentsController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('web');
    $this->middleware('admin');
  }

  public function index()
  {
    $program = new Program;
    // οι ώρες του προγράμματος
    $totalHours = $program->get_num_of_hours();
    return view('students', compact('totalHours'));
  }

  public function getStudents()
  {
    $students = Student::orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata')->with('apousies')->get();
    $arrStudents = array();
    foreach ($students as $stu) {
      $arrStudents[] = [
        'id' => $stu->id,
        'eponimo' => $stu->eponimo,
        'onoma' => $stu->onoma,
        'patronimo' => $stu->patronimo,
        'tmimata' => $stu->tmimata[0] ?? null  ? $stu->tmimata[0]->where('student_id', $stu->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->toArray() : [],
        'apousies' => $stu->apousies[0] ?? null ? $stu->apousies[0]->where('student_id', $stu->id)->pluck('apousies')->toArray() : []
      ];
    }

    $newStudents = array();
    foreach ($arrStudents as $stu) {
      $sumAp = 0;
      foreach ($stu['apousies'] as $ap) {
        $sumAp += array_sum(preg_split("//", $ap));
      }
      $newStudents[] = [
        'id' => $stu['id'],
        'eponimo' => $stu['eponimo'],
        'onoma' => $stu['onoma'],
        'patronimo' => $stu['patronimo'],
        'sumap' => $sumAp ? $sumAp : "",
        'tmimata' => join(", ", $stu['tmimata'])
      ];
    }

    return DataTables::of($newStudents)
      ->addIndexColumn()
      ->addColumn('action', function ($row) {
        $btn = '<div class=level is-mobile>
                    <a href="javascript:void(0)" class="button is-small edit level-item" id="' . $row['id'] . '">
                      <span class="icon">
                        <i class="fa fa-pencil"></i>
                        </span>
                    </a>
                    &nbsp;
                    <a href="javascript:void(0)" class="button is-small del level-item" id="' . $row['id'] . '">
                      <span class="icon">
                        <i class="fa fa-trash"></i>
                      </span>
                    </a>
                    </div>';
        return $btn;
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  public function store(Request $request)
  {
    $student = Student::updateOrCreate(
      ['id' => $request->am],
      [
        'eponimo' => $request->eponimo,
        'onoma' => $request->onoma,
        'patronimo' => $request->patronimo
      ]
    );
    Tmima::where('student_id', $student->id)->delete();

    foreach ($request->tmima as $tmima) {
      if ($tmima) {
        Tmima::updateOrCreate(['student_id' => $student->id, 'tmima' => $tmima], [
          'student_id' => $student->id,
          'tmima' => $tmima,
        ]);
      }
    }
    return response()->json(['success' => 'Student saved successfully.']);
  }

  public function edit($am)
  {
    $students = Student::where('id', $am)->with('tmimata')->get();
    $arrStudents = array();
    foreach ($students as $stu) {
      $arrStudents[] = [
        'id' => $stu->id,
        'eponimo' => $stu->eponimo,
        'onoma' => $stu->onoma,
        'patronimo' => $stu->patronimo,
        'tmimata' => $stu->tmimata[0] ?? null ? $stu->tmimata[0]->where('student_id', $stu->id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->toArray() : []
      ];
    }

    $newStudents = array();
    foreach ($arrStudents as $stu) {
      $newStudents[] = [
        'id' => $stu['id'],
        'eponimo' => $stu['eponimo'],
        'onoma' => $stu['onoma'],
        'patronimo' => $stu['patronimo'],
        't1' => isset($stu['tmimata'][0]) ? $stu['tmimata'][0] : "",
        't2' => isset($stu['tmimata'][1]) ? $stu['tmimata'][1] : "",
        't3' => isset($stu['tmimata'][2]) ? $stu['tmimata'][2] : "",
        't4' => isset($stu['tmimata'][3]) ? $stu['tmimata'][3] : "",
        't5' => isset($stu['tmimata'][4]) ? $stu['tmimata'][4] : ""
      ];
    }
    return response()->json($newStudents[0]);
  }

  public function delete($am)
  {
    Student::where('id', $am)->delete();
    Tmima::where('student_id', $am)->delete();
    return response()->json(['success' => 'Student deleted successfully.']);
  }

  public function apousies($am)
  {
    $apousies = Apousie::where('student_id', $am)->orderby('date', 'ASC')->get();

    $arrApousies = array();
    $total = 0;

    foreach ($apousies as $apou) {
      $date = Carbon::createFromFormat("!Ymd", $apou->date)->format("d/m/y");
      $sumOfDay = array_sum(preg_split("//", $apou->apousies));
      $total += $sumOfDay;
      $arrApousies[] = [
        "id" => $apou->id,
        "date" => $date,
        "total" => $total ? $total : "",
        "sumOfDay" => $sumOfDay,
        "ora1" => substr($apou->apousies, 0, 1) == "1" ? "+" : "",
        "ora2" => substr($apou->apousies, 1, 1) == "1" ? "+" : "",
        "ora3" => substr($apou->apousies, 2, 1) == "1" ? "+" : "",
        "ora4" => substr($apou->apousies, 3, 1) == "1" ? "+" : "",
        "ora5" => substr($apou->apousies, 4, 1) == "1" ? "+" : "",
        "ora6" => substr($apou->apousies, 5, 1) == "1" ? "+" : "",
        "ora7" => substr($apou->apousies, 6, 1) == "1" ? "+" : "",
      ];
    }
    return DataTables::of($arrApousies)
      ->addIndexColumn()
      ->addColumn('action', function ($row) {
        $btn = '<div class=level is-mobile>
                  <a href="javascript:void(0)" class="button is-small apouedit level-item" id="' . $row['id'] . '">
                    <span class="icon">
                      <i class="fa fa-pencil"></i>
                      </span>
                  </a>
                  &nbsp;
                  <a href="javascript:void(0)" class="button is-small apoudel level-item" id="' . $row['id'] . '">
                    <span class="icon">
                      <i class="fa fa-trash"></i>
                    </span>
                  </a>
                  </div>';
        return $btn;
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  public function apousiesEdit($id)
  {
    $program = new Program;
    // οι ώρες του προγράμματος
    $totalHours = $program->get_num_of_hours();
    $apousies = Apousie::where('id', $id)->with('student')->get();
    $arrApousies = array();
    foreach ($apousies as $apou) {
      $checker = array();
      for ($i = 0; $i < $totalHours; $i++) {
        $checker[] = substr($apou->apousies, $i, 1) == "1" ? "checked" : "";
      }
      $arrApousies[] = [
        'student_id' => $apou->student_id,
        'name' => $apou->student->eponimo . " " . $apou->student->onoma,
        'date' => Carbon::createFromFormat("!Ymd", $apou->date)->format("d/m/y"),
        'apousies' => $apou->apousies,
      ];
    }
    return $arrApousies[0];
  }

  public function apousiesStore(Request $request)
  {
    $date = Carbon::createFromFormat("!d/m/y", $request->date)->format("Ymd");
    if ($request->apousies) {
      Apousie::updateOrCreate(['student_id' => $request->student_id, 'date' => $date], [
        'apousies' => $request->apousies,
      ]);
    } else {
      Apousie::where('student_id', $request->student_id)->where('date', $date)->delete();
    }
    return response()->json(['success' => 'Student saved successfully.']);
  }

  public function apousiesDelete($id)
  {
    Apousie::where('id', $id)->delete();
    return response()->json(['success' => 'Απουσίες deleted successfully.']);
  }

  public function studentUniqueId($id)
  {
    return Student::find($id) ? 1 : 0;
  }
}
