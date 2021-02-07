<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tmima extends Model
{
  protected $fillable = [
      'student_id','tmima'
  ];
    public function student()
   {
       return $this->belongsTo('App\Student');
   }
}
