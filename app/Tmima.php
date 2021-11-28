<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Tmima extends Model
{
  protected $fillable = [
    'student_id', 'tmima'
  ];
  public function student()
  {
    return $this->belongsTo('App\Student');
  }

  public static function tmimataMaxCount()
  {
    return DB::table('tmimas')->selectRaw('count(*) as total')->groupBy('student_id')->orderByRaw('total DESC')->take(1)->pluck('total')[0] ?? null;
  }
}
