<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
  protected $fillable = [
      'id','start', 'stop'
  ];
  public function get_num_of_hours(){
    return Program::count();
  }
  public function get_active_hour($value){
    return Program::where('start', '<=', $value )->where('stop', '>=', $value )->orderby('id', 'DESC')->first()->id ?? null;
  }

}
