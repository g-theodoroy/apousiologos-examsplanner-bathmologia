<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
     protected $fillable = [
      'id','period', 'grade_id'
  ];
}
