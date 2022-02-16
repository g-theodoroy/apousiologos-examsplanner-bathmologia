<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use App\Grade;
use DataTables;
use App\Anathesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeachersController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('web');
    $this->middleware('admin');
  }

  public function index()
  {
    return view('teachers');
  }

  public function getTeachers()
  {
    // βρίσκω το id του πρώτου Διαχειριστή (συνήθως 1)
    $firstUserId = User::first()->id;

    // παίρνω τους καθηγητές
    $kathigites = User::orderby('name')->with('anatheseis')->get()->toArray();

    $arrKathigites = array();
    foreach ($kathigites as $kath) {
      usort($kath['anatheseis'], function ($a, $b) {
        return $a['tmima'] <=> $b['tmima'] ?:
          strnatcasecmp($a['mathima'],  $b['mathima']);
      });

      $anatheseis = array();
      foreach ($kath['anatheseis'] as $anath) {
        $anatheseis[] = $anath['tmima'] . " -> " . $anath['mathima'];
      }

      $arrKathigites[] = [
        'id' => $kath['id'],
        'name' => $kath['role_id'] == 1 ? "&#x26A1; " . $kath['name']  :  $kath['name'],
        'email' => $kath['email'],
        'tmimata' => $anatheseis,
      ];
    }

    return DataTables::of($arrKathigites)
      ->addIndexColumn()
      ->addColumn('action', function ($row) use ($firstUserId) {
        $btn = '<a href="javascript:void(0)" class="button is-small edit" id="' . $row['id'] . '">
                      <span class="icon">
                        <i class="fa fa-pencil"></i>
                        </span>
                    </a>';
        if ($row['id'] !== $firstUserId) {
          $btn .= '&nbsp;
                    <a href="javascript:void(0)" class="button is-small del" id="' . $row['id'] . '">
                      <span class="icon">
                        <i class="fa fa-trash"></i>
                      </span>
                    </a>';
        }
        return $btn;
      })
      ->rawColumns(['action'])
      ->escapeColumns([])
      ->make(true);
  }

  public function store(Request $request)
  {
    //πάιρνω το role_id
    // 1 = Διαχειριστής, 2 = Καθηγητής 
    $role = $request->role  ? 1 : 2;

    if ($request->id === null) {
      // δημιουργία
      $user = User::updateOrCreate(['email' => trim($request->email)], [
        'name' => trim($request->name),
        'password' => Hash::make(trim($request->password)),
        'role_id' => $role,
      ]);
    } else {
      // δεν αφήνω τον πρώτο χρήστη που γράφτηκε ως Διαχειριστής να πάψει να είναι
      if ($request->id == User::first()->id) $role = 1;
      // ενημέρωση
      $user = User::find($request->id);
      $user->name = trim($request->name);
      $user->email = trim($request->email);
      $user->role_id = $role;
      if ($request->password) $user->password = Hash::make(trim($request->password));
      $user->save();
    }
    // κρατάω τα id των υπαρχόντων αναθέσεων
    $oldAnatheseisIds = Anathesi::where('user_id', $user->id)->pluck('id');

    $newAnatheseisIds = [];
    $tmimata = explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $request->tmimata));
    // εισάγω τις καινούριες αναθέσεις ή ενημερώνω τις υπάρχουσες
    // αλλάζοντας την ανάθεση στον τρέχοντα χρήστη
    foreach ($tmimata as $tmima) {
      if (trim($tmima)) {
        $data = explode("->", $tmima);
        $newAnathesi = Anathesi::updateOrCreate(['tmima' => trim($data[0]), 'mathima' => trim($data[1] ?? null)], [
          'user_id' => $user->id,
          'tmima' => trim($data[0]),
          'mathima' => trim($data[1] ?? null),
        ]);
        // βάζω τα νέα ή ενημερωμένα id σε πίνακα
        $newAnatheseisIds[] = $newAnathesi->id;
      }
    }

    // ελέγχω αν οι παλιές αναθέσεις υπάρχουν στον πίνακα των νέων
    // και αν δεν υπάρχουν τότε
    //        αν υπάρχουν βαθμοί αποδεσμεύω την ανάθεση από τον χρήστη βάζοντας user_id = 0
    //        αν ΔΕΝ υπάρχουν βαθμοί ΔΙΑΓΡΆΦΩ την ανάθεση

    foreach ($oldAnatheseisIds as $anathId) {
      if (!in_array($anathId, $newAnatheseisIds)) {
        if (Grade::where('anathesi_id', $anathId)->count()) {
          Anathesi::where('id', $anathId)->update(['user_id' => 0]);
        } else {
          Anathesi::where('id', $anathId)->delete();
        }
      }
    }

    return response()->json(['success' => 'Teacher saved successfully.']);
  }

  public function edit($id)
  {

    $kathigites = User::where('id', $id)->with('anatheseis')->get()->toArray();
    $arrKathigites = array();
    foreach ($kathigites as $kath) {
      usort($kath['anatheseis'], function ($a, $b) {
        return $a['tmima'] <=> $b['tmima'] ?:
          strnatcasecmp($a['mathima'],  $b['mathima']);
      });

      $anatheseis = array();
      foreach ($kath['anatheseis'] as $anath) {
        $anatheseis[] = $anath['tmima'] . " -> " . $anath['mathima'];
      }

      $arrKathigites[] = [
        'id' => $kath['id'],
        'name' => $kath['name'],
        'email' => $kath['email'],
        'tmimata' => join("\n", $anatheseis),
        'role' => $kath['role_id'] == 1 ? true : false,
      ];
      return response()->json($arrKathigites[0]);
    }
  }

  public function delete($id)
  {
    User::where('id', $id)->delete();
    Anathesi::where('user_id', $id)->delete();
    return response()->json(['success' => 'Teacher deleted successfully.']);
  }

  public function uniqueEmail($email)
  {
    return User::where('email', $email)->first() ? 1 : 0;
  }
}
