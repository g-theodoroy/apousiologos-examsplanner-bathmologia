<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title', 'start', 'end', 'week', 'tmima1', 'mathima', 'tmima2', 'user_id'
    ];


    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
