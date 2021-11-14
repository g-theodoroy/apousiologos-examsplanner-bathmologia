<?php

use Illuminate\Support\Facades\Route;
use App\User;
use App\Config;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
  return view('welcome');
});

$user = new User;
$numOfAdmins = $user->get_num_of_admins();
$allowRegister = Config::getConfigValueOf('allowRegister');
if (!$allowRegister && !$numOfAdmins) $allowRegister = 1;
if (!$allowRegister) {
  Auth::routes([
    'register' => false
  ]);
} else {
  Auth::routes();
}

Route::get('/about', function () {
  return view('about');
})->name('about');

Route::get('/home/{selectedTmima?}/{date?}', 'HomeController@index')->name('home');
Route::post('/home/{selectedTmima?}/{date?}', 'HomeController@index')->name('save');
Route::get('/updated', fn () => unlink(storage_path('app/.updateCheck')))->name('updated');
Route::get('/admin', 'AdminController@index')->name('admin');
Route::get('/export', 'AdminController@export')->name('export');

Route::get('/export/apouxls', 'AdminController@exportApousiesXls')->name('apouxls');
Route::post('/export/apouxls', 'AdminController@exportApousiesXls')->name('apouxls');
Route::get('/export/kathxls', 'AdminController@exportKathigitesXls')->name('kathxls');
Route::get('/export/mathxls', 'AdminController@exportMathitesXls')->name('mathxls');
Route::get('/export/progxls', 'AdminController@exportProgramXls')->name('progxls');
Route::get('/export/apouMyschoolxls', 'AdminController@exportApousiesMyschoolXls')->name('apouMyschoolxls');

Route::post('/insertusers', 'AdminController@insertUsers')->name('insertusers');
Route::post('/insertstudents', 'AdminController@insertStudents')->name('insertstudents');
Route::post('/insertprogram', 'AdminController@insertProgram')->name('insertprogram');
Route::post('/insertMyschoolApousies', 'AdminController@insertMyschoolApousies')->name('insertMyschoolApousies');
Route::post('/set', 'AdminController@setConfigs')->name('set');

Route::get('/delkath', 'AdminController@delKathigites')->name('delkath');
Route::get('/delmath', 'AdminController@delStudents')->name('delmath');
Route::get('/delprog', 'AdminController@delProgram')->name('delprog');
Route::get('/delapou/{keep?}', 'AdminController@delApousies')->name('delapou');

Route::get('/students', 'StudentsController@index')->name('students');
Route::get('/students/getStudents', 'StudentsController@getStudents')->name('students.getStudents');
Route::post('/students', 'StudentsController@store')->name('students.store');
Route::get('/students/edit/{am?}', 'StudentsController@edit')->name('students.edit');
Route::get('/students/unique/{am?}', 'StudentsController@studentUniqueId')->name('students.unique');
Route::delete('/students/delete/{am?}', 'StudentsController@delete')->name('students.delete');
Route::get('/students/apousies/{am?}', 'StudentsController@apousies')->name('students.apousies');
Route::get('/apousies/edit/{id?}', 'StudentsController@apousiesEdit')->name('apousies.edit');
Route::post('/apousies', 'StudentsController@apousiesStore')->name('apousies.store');
Route::delete('/apousies/delete/{id?}', 'StudentsController@apousiesDelete')->name('apousies.delete');

Route::get('/teachers', 'TeachersController@index')->name('teachers');
Route::get('/teachers/getTeachers', 'TeachersController@getTeachers')->name('teachers.getTeachers');
Route::post('/teachers', 'TeachersController@store')->name('teachers.store');
Route::get('/teachers/edit/{id?}', 'TeachersController@edit')->name('teachers.edit');
Route::get('/teachers/unique/{email?}', 'TeachersController@uniqueEmail')->name('teachers.uniqueEmail');
Route::delete('/teachers/delete/{id?}', 'TeachersController@delete')->name('teachers.delete');

Route::get('/planner', 'PlannerController@index')->name('planner');

//fullcalender
Route::get('/calendar', 'FullCalendarController@index')->name('calendar');
Route::get('/calendar/data', 'FullCalendarController@data');
Route::get('/calendar/events', 'FullCalendarController@events')->name('events');;
Route::post('/calendar/tmimata', 'FullCalendarController@tmimata')->name('tmimata');
Route::post('/fullcalendar/create', 'FullCalendarController@create')->name('calendar.create');
Route::post('/fullcalendar/update', 'FullCalendarController@update');
Route::post('/fullcalendar/delete', 'FullCalendarController@destroy');

// for testing
Route::get('/calendar/tmimata', 'FullCalendarController@tmimataList');
Route::get('/calendar/conflict', 'FullCalendarController@tmimataConflict');
Route::get('/calendar/mathimata', 'FullCalendarController@mathimata');

Route::get('/grades/{selectedAnathesiId?}', 'GradeController@index')->name('grades');
Route::post('/grades/{selectedAnathesiId?}', 'GradeController@index')->name('grades.save');
Route::get('/export/gradesxls', 'GradeController@exportGradesXls')->name('gradesxls')->middleware('admin');

Route::post('/export/populateXls/{insertToDB?}', 'AdminController@populateXls')->name('populateXls')->middleware('admin');
