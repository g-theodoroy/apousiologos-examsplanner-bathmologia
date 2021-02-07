<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anathesi extends Model
{
  protected $fillable = [
    'user_id', 'tmima', 'mathima'
  ];
    public function user()
   {
       return $this->belongsTo('App\User');
   }
  public function students()
  {
    return $this->belongsToMany(Student::class, 'grades')->withPivot('grade', 'period_id');
  }

}
