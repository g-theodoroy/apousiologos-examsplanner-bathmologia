<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\User;
use App\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
	error_reporting(E_ALL ^ E_NOTICE);
      try{
        $user = new User;
        $numOfAdmins = $user->get_num_of_admins();
        $allowRegister = Config::getConfigValueOf('allowRegister');
        if (! $allowRegister && ! $numOfAdmins) $allowRegister = 1;
        View::share('allowRegister', $allowRegister);
      } catch (\Exception $e) {
        // καμία ενέργεια απλά πιάνει το λάθος
        // γιατί χτύπαγε στη δημιουργία των πινάκων με php artisan:migrate
      }

    }
}
