<?php

namespace App\Imports;

use App\User;
use App\Anathesi;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsersImport implements OnEachRow, WithStartRow
{
  protected static $user_id;
  protected static $users = 0;
  protected static $anatheseis = 0;

  public function startRow(): int
  {
    return 2;
  }

  public function onRow(Row $row)
  {
    $row      = $row->toArray();
    if (trim($row[0])) {
      $name = trim($row[0]) . " " . trim($row[1]);
      self::$users++;
      $user = User::updateOrCreate(['email' => trim($row[2])], [
        'name' => $name,
        'password' => Hash::make(trim($row[3])),
        'role_id' => 2,
      ]);

      self::$user_id = $user->id;

      if (trim($row[4])) {
        self::$anatheseis++;
        Anathesi::updateOrCreate(['user_id' => self::$user_id, 'tmima' => trim($row[4]), 'mathima' => trim($row[5])], [
          'user_id' => self::$user_id,
          'tmima' => trim($row[4]),
          'mathima' => trim($row[5]),
        ]);
      }
    } else {
      if (trim($row[4])) {
        self::$anatheseis++;
        Anathesi::updateOrCreate(['user_id' => self::$user_id, 'tmima' => trim($row[4]), 'mathima' => trim($row[5])], [
          'user_id' => self::$user_id,
          'tmima' => trim($row[4]),
          'mathima' => trim($row[5]),
        ]);
      }
     }
  }

  public function getUsersCount()
  {
    return self::$users;
  }
  public function getAnatheseisCount()
  {
    return self::$anatheseis;
  }
}
