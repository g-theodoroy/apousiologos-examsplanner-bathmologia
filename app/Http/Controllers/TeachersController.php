<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Anathesi;
use DataTables;
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
    $kathigites = User::whereRoleId(Role::whereRole('Καθηγητής')->first()->id)->orderby('name')->with('anatheseis')->get()->toArray();

    $arrKathigites = array();
    foreach ($kathigites as $kath) {
      usort($kath['anatheseis'], function ($a, $b) {
        return $a['tmima'] <=> $b['tmima'] ?:
          strnatcasecmp($a['mathima'],  $b['mathima']);
      });

      $anatheseis = array();
      foreach ($kath['anatheseis'] as $anath) {
        $anatheseis[] = $anath['tmima'] . " -> " . $anath['mathima'] ;
      }

      $arrKathigites[] = [
        'id' => $kath['id'],
        'name' => $kath['name'],
        'email' => $kath['email'],
        'tmimata' => $anatheseis,
      ];
    }

    return DataTables::of($arrKathigites)
      ->addIndexColumn()
      ->addColumn('action', function ($row) {
        $btn = '<a href="javascript:void(0)" class="button is-small edit" id="' . $row['id'] . '">
                      <span class="icon">
                        <i class="fa fa-pencil"></i>
                        </span>
                    </a>
                    &nbsp;
                    <a href="javascript:void(0)" class="button is-small del" id="' . $row['id'] . '">
                      <span class="icon">
                        <i class="fa fa-trash"></i>
                      </span>
                    </a>';
        return $btn;
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  public function store(Request $request)
  {
    if (!$request->id) {
      $user = User::updateOrCreate(['email' => trim($request->email)], [
        'name' => trim($request->name),
        'password' => Hash::make(trim($request->password)),
        'role_id' => 2,
      ]);
    } else {
      $user = User::find($request->id);
      $user->name = trim($request->name);
      $user->email = trim($request->email);
      if ($request->password) $user->password = Hash::make(trim($request->password));
      $user->save();
    }

    Anathesi::where('user_id', $user->id)->delete();
    $tmimata = explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $request->tmimata));

    foreach ($tmimata as $tmima) {
      if (trim($tmima)) {
        $data = explode ("->", $tmima);
        Anathesi::updateOrCreate(['user_id' => $user->id, 'tmima' => trim($data[0]), 'mathima' => trim($data[1])], [
          'user_id' => $user->id,
          'tmima' => trim($data[0]),
          'mathima' => trim($data[1]),
        ]);
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
}
