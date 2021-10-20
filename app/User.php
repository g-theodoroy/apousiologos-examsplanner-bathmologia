<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function anatheseis()
    {
        return $this->hasMany('App\Anathesi');
    }

    public function events()
    {
        return $this->hasMany('App\Event');
    }

   
    public function get_num_of_admins()
    {
        if (!Schema::hastable('roles')) {
            return ;
        }
        return User::whereRoleId(Role::whereRole('Διαχειριστής')->first()->id)->count();
    }
    public static function get_num_of_kathigites()
    {
        if (!Schema::hastable('roles')) {
            return ;
        }
        return User::whereRoleId(Role::whereRole('Καθηγητής')->first()->id)->count();
    }


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
