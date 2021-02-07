<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Apousie extends Model
{
  protected $fillable = [
      'student_id', 'date', 'apousies'
  ];
  public function student()
 {
     return $this->belongsTo('App\Student');
 }
 public function apousiesDaysCount()
{
    return Apousie::distinct('date')->count();
}
}
