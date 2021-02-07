<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function users(){
    	return $this->hasMany('App\User','role_id');
    }

        /**
     * Βρίσκω τον id του Διαχειριστή
     *
     * @var array
     */
    public function get_admin_id(){
        return Role::where('role','Διαχειριστής')->first()->id;
    }

}
