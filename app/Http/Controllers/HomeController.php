<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Student;
use App\Tmima;
use App\Program;
use App\Config;
use App\Anathesi;
use App\Apousie;
use Carbon\Carbon;
use Session;

class HomeController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function index($selectedTmima = 0, $date = null)
  {
    // μεταβλητές
    $isAdmin = Auth::user()->role->role == "Διαχειριστής";
    $settings = Config::getConfigValues();
    $hoursUnlocked = 0;
    $today = Carbon::now()->format("Ymd");

    if ($isAdmin) {
      if (strpos(request()->headers->get('referer'), 'login')) {
        $this->chkForUpdates();
      } else {
        Session::forget('notification');
      }
    }


    // Αν έχει υποβληθεί η φόρμα
    if (request()->method() == 'POST') {

      // παίρνω την ημέρα και αλλάζω το format της ημνιας από ηη/μμ/εε σε εεεεμμηη
      $date = Carbon::createFromFormat("!d/m/y", request()->date)->format("Ymd");

      // παίρνω τα στοιχεία των απουσιών (συμβολοσειρά 7 αριθμών 0 ή 1 πχ '1111100')
      $data = request()->except(['_token', 'date']);

      // όπου έχει στοιχεία
      foreach ($data as $key => $value) {

        // αν δεν είναι κενό ενημερώνω αν υπάρχει ΑΜ+ημνια ή πρόσθέτω
        if ($value) {
          Apousie::updateOrCreate(['student_id' => substr($key, 2), 'date' => $date], [
            'apousies' => $value,
          ]);
        } else {
          // αν κενό διαγράφω αν υπάρχει ΑΜ+ημνια
          Apousie::where('student_id', substr($key, 2))->where('date', $date)->delete();
        }
      }
    } // τέλος Αν έχει υποβληθεί η φόρμα

    // αρχικοποιώ την ημέρα αν δεν έχει έρθει με το url
    if (!$date) $date = $today;
    // αν έχει οριστεί συγκεκριμμένη ημέρα από τον Διαχειριστή
    $setCustomDate = $settings['setCustomDate'];
    // αν ο χρήστης δεν είναι Διαχειριστής
    if (!$isAdmin) {
      if ($setCustomDate) {
        // ή η συγκεκριμμένη ημέρα
        $date = Carbon::createFromFormat("!d/m/y", $setCustomDate)->format("Ymd");
      } else {
        // έλεγχος ότι ο μη διαχειριστής δεν μπορεί να πάει
        // σε μελλοντική ημερομηνία ή παρελθούσα πριν την μέγιστη επιτρεπόμενη
        if ($settings['pastDaysInsertApousies']) {
          $lastPreviousDay = Carbon::now()->subDays($settings['pastDaysInsertApousies'])->format("Ymd");
          if ($date > $today) $date = $today;
          if ($date < $lastPreviousDay) $date = $lastPreviousDay;
        } else {
          $date = $today;
        }
      }
    }

    // παίρνω τα τμηματα του χρήστη
    // ταξινόμηση με το μήκος του ονόματος + αλφαβητικά
    $anatheseis = Auth::user()->anatheseis->sortBy('LENGTH(tmima)')->sortBy('tmima')->pluck('tmima');

    // αν είναι Διαχειριστής τα παίρνω όλα από μια φορά
    if ($isAdmin) {
      $anatheseis = Anathesi::orderByRaw('LENGTH(tmima)')->orderby('tmima')->distinct()->pluck('tmima');
    }

    // αν το τμήμα που δόθηκε στο url δεν αντιστοιχεί στον χρήστη επιστρέφω πίσω
    if ($selectedTmima && !$anatheseis->contains($selectedTmima)) return back();

    // βάζω σε πίνακα [ΑΜ]=απουσίες για την ημέρα
    $apousiesForDate = Apousie::where('date', $date)->pluck('apousies', 'student_id')->toArray();

    if ($selectedTmima) {
      // βάζω σε ένα πίνακα τους ΑΜ των μαθητών που ανήκουν στο επιλεγμένο τμήμα
      $student_ids = Tmima::where('tmima', $selectedTmima)->pluck('student_id')->toArray();

      // παίρνω τα στοιχεία των μαθητών ταξινομημένα κσι φιλτράρω μόνο τους ΑΜ που έχει το τμήμα
      $students = Student::select('id', 'eponimo', 'onoma', 'patronimo')->orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata:student_id,tmima')->get()->only($student_ids);
    } else { // δεν είναι επιλεγμένο τμήμα = όλοι όσοι έχουν απουσίες
      // βρίσκω τους μαθητές που έχουν απουσίες την συγκεκριμμένη ημέρα
      $students = Student::select('id', 'eponimo', 'onoma', 'patronimo')
        ->whereHas('apousies', function ($query) use ($date) {
          $query->where('date', '=', $date);
        })->orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata:student_id,tmima')->get('id', 'eponimo', 'onoma', 'patronimo');
    }

    // φτιάχνω πίνακα με τα στοιχεία που θα εμφανίσω
    $arrStudents = array();
    foreach ($students as $stuApFoD) {
      $tmimata = $stuApFoD->tmimata->pluck('tmima');
      $arrStudents[] = [
        'id' => $stuApFoD->id,
        'eponimo' => $stuApFoD->eponimo,
        'onoma' => $stuApFoD->onoma,
        'patronimo' => $stuApFoD->patronimo,
        // παίρνω το πρώτο τμήμα με το λιγότερο μήκος σαν βασικό τμήμα
        // υποθέτοντας ότι συνήθως τα τμήματα γενικής τα γράφουμε σύντομα πχ Α1 αντί Α1-ΑΓΓΛΙΚΑ
        'tmima' => $tmimata[0],
        // παίρνω όλα τα τμήματα και φτιάχνω string χωρισμένο με κόμμα (,)
        'tmimata' => $tmimata->implode(', '),
        // αν υπάρχουν απουσίες για την συγκεκριμμένη ημέρα  για το μαθητή. Μορφή: '1111000' 
        'apousies' => $apousiesForDate[$stuApFoD->id] ?? null
      ];
    }
    // ταξινόμηση πίνακα
    // αν έχει επιλεγεί τμήμα ταξινόμηση με το Επώνυμο Όνομα
    if ($selectedTmima) {
      usort($arrStudents, function ($a, $b) {
        return $a['eponimo'] <=> $b['eponimo'] ?:
          $a['onoma'] <=> $b['onoma'] ?:
          strnatcasecmp($a['patronimo'], $b['patronimo']);
      });
      // αν δεν έχει επιλεγεί τμήμα ταξινόμηση με το Τμήμα Επώνυμο Όνομα
    } else {
      usort($arrStudents, function ($a, $b) {
        return $a['tmima'] <=> $b['tmima'] ?:
          $a['eponimo'] <=> $b['eponimo'] ?:
          $a['onoma'] <=> $b['onoma'] ?:
          strnatcasecmp($a['patronimo'], $b['patronimo']);
      });
    }

    // φτιάχνω πίνακα με τις απουσίες της ημέρας
    // ανά ΤΑΞΗ
    // πόσες είναι ίσες με 1, 2, 3, 4, 5, 6, 7
    // πόσες είναι πάνω από 1, 2, 3, 4, 5, 6

    // παίρνω το 1ο γράμμα του τμήματος σαν τάξη: Α1 -> Α ΒΘΕΤ -> Β
    $taxeis = array();
    foreach ($arrStudents as $stu) {
      if (!in_array(mb_substr($stu['tmima'], 0, 1), $taxeis)) $taxeis[] = mb_substr($stu['tmima'], 0, 1);
    }

    // αρχικοποίηση του πίνακα με 0
    $sumApousies = array();
    foreach ($taxeis as $taxi) {
      for ($i = 1; $i < 8; $i++) {
        $sumApousies[$taxi]['equal'][$i] = 0;
      }
      for ($i = 1; $i < 7; $i++) {
        $sumApousies[$taxi]['over'][$i] = 0;
      }
    }
    for ($i = 1; $i < 8; $i++) {
      $sumApousies['sums']['equal'][$i] = 0;
    }
    for ($i = 1; $i < 7; $i++) {
      $sumApousies['sums']['over'][$i] = 0;
    }

    // αποθηκεύω την αρχική κατάσταση με 0
    $sumApousiesCheck = $sumApousies;
    // για κάθε μαθητή
    foreach ($arrStudents as $stu) {
      // προσθέτω τις απουσίες "0001111" -> 4 
      $appSum = array_sum(preg_split("//", $stu['apousies']));
      // για κάθε τάξη
      foreach ($taxeis as $taxi) {
        // αν ο μαθητής είναι στην τάξη = 1ο γράμμα τμήματος
        if ($taxi == mb_substr($stu['tmima'], 0, 1)) {
          for ($i = 1; $i < 8; $i++) {
            // προσθέτω πόσες φορές το άθροισμα είναι ίσο με τη τιμή 1,2,3,4,5,6,7
            if ($appSum == $i) $sumApousies[$taxi]['equal'][$i] = $sumApousies[$taxi]['equal'][$i] + 1;
          }
          for ($i = 1; $i < 7; $i++) {
            // προσθέτω πόσες φορές το άθροισμα είναι ίσο ή μεγαλύτερο από τη τιμή 1,2,3,4,5,6
            if ($appSum >= $i) $sumApousies[$taxi]['over'][$i] = $sumApousies[$taxi]['over'][$i] + 1;
          }
        }
      }
      for ($i = 1; $i < 8; $i++) {
        // γενικά σύνολα όλων των τάξεων πόσες φορές το άθροισμα είναι ίσο με τη τιμή 1,2,3,4,5,6,7
        if ($appSum == $i) $sumApousies['sums']['equal'][$i] = $sumApousies['sums']['equal'][$i] + 1;
      }
      for ($i = 1; $i < 7; $i++) {
        // γενικά σύνολα όλων των τάξεων πόσες φορές το άθροισμα είναι ίσο ή μεγαλύτερο από τη τιμή 1,2,3,4,5,6
        if ($appSum >= $i) $sumApousies['sums']['over'][$i] = $sumApousies['sums']['over'][$i] + 1;
      }
    }

    // αν δεν προστέθηκαν απουσίες αδειάζω τελείως τον πίνακα $sumApousies
    if ($sumApousiesCheck == $sumApousies) $sumApousies = [];

    //διαβάζω ρυθμίσεις από τον πίνακα configs
    $program = new Program;
    // οι ώρες του προγράμματος
    $totalHours = $program->get_num_of_hours();
    // η ζώνη ώρας
    $timeZone = $settings['timeZone'];
    // βρίσκω την ενεργή ώρα για πέρασμα απουσιών
    // σε παρελθούσα ημνια δεν έχω ενεργή ώρα
    $activeHour = $program->get_active_hour(Carbon::Now($timeZone)->format("Hi"));
    if ($date !== Carbon::now($timeZone)->format("Ymd")) $activeHour = 0;
    // αν είναι ΣΚ 
    $isWeekend = Carbon::createFromFormat("!Ymd", $date)->isWeekend();
    // επιτρέπεται η καταχώριση το ΣΚ
    $allowWeekends = $settings['allowWeekends'];
    // αν θέλουμε τις ώρες ξεκλείδωτες ή είμαστε Διαχειριστής
    if ($settings['hoursUnlocked'] || $isAdmin) $hoursUnlocked = 1;
    // επιτρέπεται στους να ξεκλειδώσουν τις ώρες;
    $letTeachersUnlockHours = $settings['letTeachersUnlockHours'];
    // να φαίνονται ή όχι οι επόμενες ώρες
    $showFutureHours = $settings['showFutureHours'];
    // παίρνω την ημέρα και αλλάζω το format της ημνιας από εεεεμμηη σε ηη/μμ/εε
    $date = Carbon::createFromFormat("!Ymd", $date)->format("d/m/y");
    // αν έχει οριστεί συγκεκριμμένη ημέρα
    // ξεκλειδώνω τις ώρες
    if ($setCustomDate) {
      $hoursUnlocked = 1;
    }
    if ($settings['pastDaysInsertApousies'] && $date !== $today) {
      $hoursUnlocked = 1;
    }
    $allowTeachersSaveAtNotActiveHour = $settings['allowTeachersSaveAtNotActiveHour'];

    return view('home', compact('date', 'anatheseis', 'selectedTmima', 'totalHours', 'activeHour', 'hoursUnlocked', 'letTeachersUnlockHours', 'showFutureHours', 'arrStudents', 'taxeis', 'sumApousies', 'setCustomDate', 'allowTeachersSaveAtNotActiveHour', 'isWeekend', 'allowWeekends'));
  }

  private function chkForUpdates()
  {
    try {
      // έλεγχος εάν έχουν γίνει αλλαγές στο github
      $url = 'https://api.github.com/repos/g-theodoroy/apousiologos-examsplanner-bathmologia/commits';
      $opts = ['http' => ['method' => 'GET', 'header' => ['User-Agent: PHP']]];
      $context = stream_context_create($opts);
      $json = file_get_contents($url, false, $context);
      $commits = json_decode($json, true);
    } catch (\Throwable $e) {
      report($e);
      $commits = null;
    }
    // εάν υπάρχουν commits
    if ($commits) {
      if (Auth::user()->role_id == 1) {
        $message = 'Έγιναν τροποποιήσεις στον κώδικα του Ηλ.Απουσιολόγου στο Github.<br><br>Αν επιθυμείτε <a href=\"https://github.com/g-theodoroy/apousiologos-examsplanner-bathmologia/commits/\" target=\"_blank\"><u> εξετάστε τον κώδικα</u></a> και ενημερώστε την εγκατάστασή σας.<br><br>Για να μην εμφανίζεται το παρόν μήνυμα καντε κλικ στο κουμπί Ενημερώθηκε.';
        // διαβάζω από το αρχείο .updateCheck το id του τελευταίου αποθηκευμένου commit
        $file = storage_path('app/.updateCheck');
        if (file_exists($file)) {
          // αν διαφέρει με το id του τελευταίου commit στο github
          // στέλνω ειδοποίηση για την υπάρχουσα ενημέρωση
          if ($commits[0]['sha'] != file_get_contents($file)) {
            $notification = array(
              'message' =>  $message,
            );
            session()->flash('notification', $notification);
          }
        } else {
          // αν δεν υπάρχει το αρχείο .updateCheck το
          // δημιουργώ και γράφω το id του τελευταίου commit
          file_put_contents($file, $commits[0]['sha']);
        }
      }
    }
  }
}
