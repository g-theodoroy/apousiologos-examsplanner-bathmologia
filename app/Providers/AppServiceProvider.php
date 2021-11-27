<?php

namespace App\Providers;

use App\User;
use App\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
    try {
      $user = new User;
      $numOfAdmins = $user->get_num_of_admins();
      $allowRegister = Config::getConfigValueOf('allowRegister');
      if (!$allowRegister && !$numOfAdmins) $allowRegister = 1;
      View::share('allowRegister', $allowRegister);
      $schoolName = Config::getConfigValueOf('schoolName');
      View::share('schoolName', $schoolName);
      $activeGradePeriod = Config::getConfigValueOf('activeGradePeriod');
      View::share('activeGradePeriod', $activeGradePeriod);
    } catch (\Exception $e) {
      // καμία ενέργεια απλά πιάνει το λάθος
      // γιατί χτύπαγε στη δημιουργία των πινάκων με php artisan:migrate
    }
    Blade::if('admin', function () {
      return Auth::user()->role->role == "Διαχειριστής";
    });
  }
}
