@extends('layouts.app')

@section('content')

<div class="container">

  <div class="columns is-marginless is-centered">
    <div class="column is-10">
      <nav class="card">
        <header class="card-header">
          <p class="card-header-title">
            Ρυθμίσεις
          </p>
          @if(Session::get('setDone'))
          <p class="card-header-title has-text-success">
            Έγινε ενημέρωση των ρυθμίσεων
          </p>
          @endif
        </header>

        <form name="formSet" id="formSet" role="form" method="POST" action="{{ url('/set') }}" onsubmit="return chkCustomDateFormat()">
          {{ csrf_field() }}
          <div class="card-content">
            <div class="columns is-centered">
              <div class="column is-narrow">
                <table class="table">
                  <tr><td colspan="2"  class="has-text-centered"><b>Σύστημα</b></td></>
                  <tr>
                    <td>Να επιτρέπεται η Εγγραφή νέων χρηστών</td>
                    <td class="has-text-centered"><input name="allowRegister" type="checkbox" @if(App\Config::getConfigValueOf('allowRegister')) checked @endif></td>
                  </tr>
                  <tr>
                    <td>Όνομα σχολείου</td>
                    <td class="has-text-centered"><input name="schoolName" class="input has-text-centered" type="text" value="{{App\Config::getConfigValueOf('schoolName')}}"></td>
                  </tr>
                  <tr>
                    <td>Ζώνη ώρας</td>
                    <td class="has-text-centered"><input name="timeZone" class="input has-text-centered" type="text" value="{{App\Config::getConfigValueOf('timeZone')}}"></td>
                  </tr>
                  <tr><td colspan="2"  class="has-text-centered"><b>Απουσιολόγος</b></td></>
                  <tr>
                    <td>Οι ώρες να είναι ξεκλείδωτες</td>
                    <td class="has-text-centered"><input name="hoursUnlocked" type="checkbox" @if(App\Config::getConfigValueOf('hoursUnlocked')) checked @endif></td>
                  </tr>
                  <tr>
                  <tr>
                    <td>Επιτρέπεται η εισαγωγή απουσιών εκτός ωραρίου</td>
                    <td class="has-text-centered"><input name="allowTeachersSaveAtNotActiveHour" type="checkbox" @if(App\Config::getConfigValueOf('allowTeachersSaveAtNotActiveHour')) checked @endif></td>
                  </tr>
                  <tr>
                    <td>Επιτρέπεται στους καθηγητές να ξεκλειδώνουν τις ώρες</td>
                    <td class="has-text-centered"><input name="letTeachersUnlockHours" type="checkbox" @if(App\Config::getConfigValueOf('letTeachersUnlockHours')) checked @endif></td>
                  </tr>
                  <tr>
                    <td>Να μη κρύβονται οι επόμενες ώρες</td>
                    <td class="has-text-centered"><input name="showFutureHours" type="checkbox" @if(App\Config::getConfigValueOf('showFutureHours')) checked @endif></td>
                  </tr>
                  <tr>
                    <td>Επιτρέπεται η εισαγωγή απουσιών Σαββατοκύριακο</td>
                    <td class="has-text-centered"><input name="allowWeekends" type="checkbox" @if(App\Config::getConfigValueOf('allowWeekends')) checked @endif></td>
                  </tr>
                  <tr>
                  <tr>
                    <td>Οι καθηγητές μπορούν να εισάγουν απουσίες {{App\Config::getConfigValueOf('pastDaysInsertApousies')}} ημέρες πίσω</td>
                    <td class="has-text-centered"><input id="pastDaysInsertApousies" name="pastDaysInsertApousies" class="input has-text-centered" type="text" value="{{App\Config::getConfigValueOf('pastDaysInsertApousies')}}" ></td>
                  </tr>
                  <tr>
                    <td>Ορισμός Ημνιας εισαγωγής απουσιών</td>
                    <td class="has-text-centered"><input id="setCustomDate" name="setCustomDate" class="input has-text-centered" type="text" value="{{App\Config::getConfigValueOf('setCustomDate')}}" placeholder="ηη/μμ/εε" ></td>
                  </tr>
                  <tr><td colspan="2" class="has-text-centered" ><b>Προγραμματισμός διαγωνισμάτων</b></td></>
                  <tr>
                    <td>Επιτρεπόμενα διαγωνίσματα την ημέρα</td>
                    <td class="has-text-centered"><input name="maxDiagonismataForDay" class="input has-text-centered" type="text" value="{{App\Config::getConfigValueOf('maxDiagonismataForDay')}}"></td>
                  </tr>
                  <tr>
                    <td>Επιτρεπόμενα διαγωνίσματα την εβδομάδα</td>
                    <td class="has-text-centered"><input name="maxDiagonismataForWeek" class="input has-text-centered" type="text" value="{{App\Config::getConfigValueOf('maxDiagonismataForWeek')}}"></td>
                  </tr>
                  <tr>
                    <td>Αρχή σχολικού έτους "Μήνας-Ημέρα"</td>
                    <td class="has-text-centered"><input name="totalStartMonthDay" class="input has-text-centered" type="text" value="{{App\Config::getConfigValueOf('totalStartMonthDay')}}" placeholder="μμ-ηη" ></td>
                  </tr>
                  <tr>
                    <td>Τέλος σχολικού έτους "Μήνας-Ημέρα"</td>
                    <td class="has-text-centered"><input name="totalEndMonthDay" class="input has-text-centered" type="text" value="{{App\Config::getConfigValueOf('totalEndMonthDay')}}" placeholder="μμ-ηη" ></td>
                  </tr>
                  <tr><td colspan="2" class="has-text-centered" ><b>Κατάθεση βαθμολογίας</b></td></tr>
                  <tr>
                    <td>Βαθμολογική περίοδος</td>
                    <td class="has-text-centered">
                      <div class="select has-text-centered">
                      <select name="activeGradePeriod">
                        <option value="0"> --- </option>
                        @foreach (\App\Period::all() as $period)
                          <option value="{{ $period->id}}" @if($period->id == App\Config::getConfigValueOf('activeGradePeriod')) selected @endif>{{ $period->period}}</option>
                        @endforeach
                      </select>
                      </div>
                   </td>
                  </tr>
                  <tr>
                  <tr>
                    <td>Εμφάνιση βαθμών άλλων μαθημάτων</td>
                    <td class="has-text-centered">
                      <div class="select has-text-centered">
                      <select name="showOtherGrades">
                        <option value="0" @if(! App\Config::getConfigValueOf('showOtherGrades')) selected @endif >OXI</option>
                        <option value="1" @if(  App\Config::getConfigValueOf('showOtherGrades')) selected @endif >ΝΑΙ</option>
                      </select>
                      </div>
                   </td>
                  </tr>
                  <tr>
                  <td class="has-text-centered" colspan="2">
                    <button class="button" type="submit">
                      <span class="icon">
                        <i class="fa fa-trash"></i>
                      </span>
                      <span>Αποθήκευση</span>
                    </button>
                  </td>
                  <tr>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </form>
      </nav>
    </div>
  </div>




  <div class="columns is-marginless is-centered">
    <div class="column is-10">
      <nav class="card">
        <header class="card-header">
          <p class="card-header-title">
            @if($kathigitesCount)
            Καθηγητές: {{$kathigitesCount}}
            @else
            Εισαγωγή Καθηγητών
            @endif
          </p>
          @if(Session::get('insertedUsersCount'))
          <p class="card-header-title has-text-success">
            Έγινε εισαγωγή-ενημέρωση {{Session::get('insertedUsersCount')}} καθηγητών
            και {{Session::get('insertedAnatheseisCount')}} αναθέσεων
          </p>
          @endif
          @if(Session::get('delKathigitesCount'))
          <p class="card-header-title has-text-success">
            Έγινε διαγραφή {{Session::get('delKathigitesCount')}} καθηγητών
          </p>
          @endif
        </header>
        <form name="formKath" id="formKath" role="form" method="POST" action="{{ url('/insertusers') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-content">
            <nav class="level is-mobile">
              <a class="button level-item" onclick="chkDelKathigites(event)" @if(!$kathigitesCount) disabled @endif>
                <span class="icon">
                  <i class="fa fa-trash"></i>
                </span>
                <span>Διαγραφή</span>
              </a>
              <div id="file-kathigites" class="file has-name level-item has-text-centered">
                <label class="file-label">
                  <input class="file-input" type="file" name="file_kath">
                  <span class="file-cta">
                    <span class="file-icon">
                      <i class="fa fa-search"></i>
                    </span>
                    <span class="file-label">
                      Επιλογή xls
                    </span>
                  </span>
                  <span class="file-name">
                    ---
                  </span>
                </label>
              </div>
              <button id="sbmt_kath" class="button level-item" type="submit" disabled>
                <span class="icon">
                  <i class="fa fa-upload"></i>
                </span>
                <span>Εισαγωγή xls</span>
              </button>
              <a class="button level-item" href="{{ url('/export/kathxls') }}">
                <span class="icon">
                  <i class="fa fa-download"></i>
                </span>
                <span>@if($kathigitesCount) Εξαγωγή xls @else Πρότυπο xls @endif</span>
              </a>
            </nav>
          </div>
        </form>
      </nav>
    </div>
  </div>


  <div class="columns is-marginless is-centered">
    <div class="column is-10">
      <nav class="card">
        <header class="card-header">
          <p class="card-header-title">
            @if($studentsCount)
            Μαθητές: {{$studentsCount}}
            @else
            Εισαγωγή Μαθητών
            @endif
          </p>
          @if(Session::get('insertedStudentsCount'))
          <p class="card-header-title has-text-success">
            Έγινε εισαγωγή-ενημέρωση {{Session::get('insertedStudentsCount')}} μαθητών
            και {{Session::get('insertedTmimataCount')}} τμημάτων
          </p>
          @endif
          @if(Session::get('delStudentsCount'))
          <p class="card-header-title has-text-success">
            Έγινε διαγραφή {{Session::get('delStudentsCount')}} μαθητών
          </p>
          @endif
        </header>

        <form name="formMath" id="formMath" role="form" method="POST" action="{{ url('/insertstudents') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-content">
            <nav class="level is-mobile">
              <a class="button level-item" onclick="chkDelStudents(event)" @if(!$studentsCount) disabled @endif>
                <span class="icon">
                  <i class="fa fa-trash"></i>
                </span>
                <span>Διαγραφή</span>
              </a>
              <div id="file-mathites" class="file has-name level-item has-text-centered">
                <label class="file-label">
                  <input class="file-input" type="file" name="file_math">
                  <span class="file-cta">
                    <span class="file-icon">
                      <i class="fa fa-search"></i>
                    </span>
                    <span class="file-label">
                      Επιλογή xls
                    </span>
                  </span>
                  <span class="file-name">
                    ---
                  </span>
                </label>
              </div>
              <button id="sbmt_math" class="button level-item" type="submit" disabled>
                <span class="icon">
                  <i class="fa fa-upload"></i>
                </span>
                <span>Εισαγωγή xls</span>
              </button>
              <a class="button level-item" href="{{ url('/export/mathxls') }}">
                <span class="icon">
                  <i class="fa fa-download"></i>
                </span>
                <span>@if($studentsCount) Εξαγωγή xls @else Πρότυπο xls @endif</span>
              </a>
            </nav>
          </div>
        </form>
      </nav>
    </div>
  </div>

  <div class="columns is-marginless is-centered">
    <div class="column is-10">
      <nav class="card">
        <header class="card-header">
          <p class="card-header-title">
            @if($programCount)
            Ώρες: {{$programCount}}
            @else
            Εισαγωγή προγράμματος
            @endif
          </p>
          @if(Session::get('insertedProgram'))
          <p class="card-header-title has-text-success">
            Έγινε εισαγωγή του προγράμματος
          </p>
          @endif
          @if(Session::get('deletedProgram'))
          <p class="card-header-title has-text-success">
            Έγινε διαγραφή του προγράμματος
          </p>
          @endif
        </header>

        <form name="formProg" id="formProg" role="form" method="POST" action="{{ url('/insertprogram') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-content">
            <nav class="level is-mobile">
              <a class="button level-item" onclick="chkDelProgram(event)" @if(!$programCount) disabled @endif>
                <span class="icon">
                  <i class="fa fa-trash"></i>
                </span>
                <span>Διαγραφή</span>
              </a>
              <div id="file-program" class="file has-name level-item has-text-centered">
                <label class="file-label">
                  <input class="file-input" type="file" name="file_prog">
                  <span class="file-cta">
                    <span class="file-icon">
                      <i class="fa fa-search"></i>
                    </span>
                    <span class="file-label">
                      Επιλογή xls
                    </span>
                  </span>
                  <span class="file-name">
                    ---
                  </span>
                </label>
              </div>
              <button id="sbmt_prog" class="button level-item" type="submit" disabled>
                <span class="icon">
                  <i class="fa fa-upload"></i>
                </span>
                <span>Εισαγωγή xls</span>
              </button>
              <a class="button level-item" href="{{ url('/export/progxls') }}">
                <span class="icon">
                  <i class="fa fa-download"></i>
                </span>
                <span>@if($programCount) Εξαγωγή xls @else Πρότυπο xls @endif</span>
              </a>
            </nav>
          </div>
        </form>
      </nav>
    </div>
  </div>

  <div class="columns is-marginless is-centered">
    <div class="column is-10">
      <nav class="card">
        <header class="card-header">
          <p class="card-header-title">
            Εισαγωγή απουσιών από το myschool
          </p>
          @if(Session::get('insertedStudentsApousiesCount'))
          <p class="card-header-title has-text-success">
            Έγινε εισαγωγή-ενημέρωση {{Session::get('insertedDaysApousiesCount')}} ημερών με απουσίες
            για {{Session::get('insertedStudentsApousiesCount')}} μαθητές
          </p>
          @endif
        </header>

        <form name="formMyschApou" id="formMyschApou" role="form" method="POST" action="{{ url('/insertMyschoolApousies') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-content">
            <nav class="level is-mobile">
              <div id="file-myschApou" class="file has-name level-item has-text-centered">
                <label class="file-label">
                  <input class="file-input" type="file" name="file_myschAp">
                  <span class="file-cta">
                    <span class="file-icon">
                      <i class="fa fa-search"></i>
                    </span>
                    <span class="file-label">
                      Επιλογή xls
                    </span>
                  </span>
                  <span class="file-name">
                    ---
                  </span>
                </label>
              </div>
              <button id="sbmt_myschAp" class="button level-item" type="submit" disabled>
                <span class="icon">
                  <i class="fa fa-upload"></i>
                </span>
                <span>Εισαγωγή xls</span>
              </button>
              <a class="button level-item" href="{{ url('/export/apouMyschoolxls') }}">
                <span class="icon">
                  <i class="fa fa-download"></i>
                </span>
                <span>Πρότυπο xls</span>
              </a>
            </nav>
            <nav>
              <p class="help">Κάνοντας εισαγωγή απουσιών από το Myschool δεν έχουμε δεδομένα για την ώρα (1η - 7η) που έγιναν οι απουσίες.
                Οι απουσίες που δεν είναι καταχωρισμενες ή διαφέρουν (περισσότερες ή λιγότερες) από τις εισηγμένες καταχωρίζονται ως εξής:
                <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> περισσότερες -> προστίθενται στις πρώτες ώρες χωρίς απουσία
                <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-> λιγότερες -> αφαιρούνται από τις τελευταίες ώρες με απουσία</p>
            </nav>
          </div>
        </form>
      </nav>
    </div>
  </div>

          <div class="columns is-marginless is-centered">
            <div class="column is-10">
                <nav class="card">
                    <header class="card-header">
                    <p class="card-header-title">
                        Εισαγωγή βαθμών από αρχείο 187.xls 
                    </p>
                    @if(Session::get('insertedGradesCount'))
                    <p class="card-header-title has-text-success">
                      Έγινε εισαγωγή-ενημέρωση {{Session::get('insertedGradesCount')}} βαθμών
                    </p>
                    @endif
                    </header>

                    <form name="frm" id="frm" role="form" method="POST" action="{{ url('export/populateXls/1') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="card-content">
                        <nav class="level">

                        @if( \App\Config::getConfigValueOf('activeGradePeriod'))

                            <div class="level-item">
                                <p class="card-header-title">
                                    {{\App\Period::find(\App\Config::getConfigValueOf('activeGradePeriod'))->period}}
                                </p>
                            </div>
                                        
                            <div id="file-xls" class="file has-name level-item ">
                                <label class="file-label">
                                <input class="file-input" type="file" name="file_xls">
                                <span class="file-cta">
                                    <span class="file-icon">
                                    <i class="fa fa-search"></i>
                                    </span>
                                    <span class="file-label">
                                    Επιλογή xls
                                    </span>
                                </span>
                                <span class="file-name">
                                    ---
                                </span>
                                </label>
                            </div>

                            <div class="level-item">
                                <button id="sbmt_xls" class="button" type="submit" disabled>
                                <span class="icon">
                                <i class="fa fa-upload"></i>
                                </span>
                                <span>Εισαγωγή βαθμών</span>
                                </button>
                            </div>

                        </nav>
                    </div>
                    <div class="card-content">
                        <nav class="level">

                                <div class="level-item">
                                    <div class="field-label is-normal">
                                        <label class="label">Γραμμή&nbsp;επικεφαλίδων</label>
                                    </div>
                                    <div class="field-body">
                                        <div>
                                            <p class="control">
                                                <input name="labelsRow" class="input" type="text"
                                                    size="1" value="{{ App\Config::getconfigValueOf('187XlsLabelsRow') }}" >
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                        
                                <div class="level-item">
                                    <div class="field-label is-normal">
                                        <label class="label">Κολώνα&nbsp;Αρ.&nbsp;Μητρώου</label>
                                    </div>
                                    <div class="field-body">
                                        <div>
                                            <p class="control">
                                                <input name="amCol" class="input" type="text"
                                                    size="1" value="{{ App\Config::getconfigValueOf('187XlsAmCol') }}" >
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="level-item">
                                    <div class="field-label is-normal">
                                        <label class="label">Κολώνα&nbsp;1ου&nbsp;μαθήματος</label>
                                    </div>
                                    <div class="field-body">
                                        <div>
                                            <p class="control">
                                                <input name="firstLessonCol" class="input" type="text"
                                                    size="1" value="{{ App\Config::getconfigValueOf('187XlsFirstLessonCol') }}" >
                                            </p>
                                        </div>
                                    </div>
                                </div>  
                                
                                 @else

                                    <div class="level-item">
                                        <p class="card-header-title">
                                        Επιλέξτε Βαθμολογική περίοδο στις &nbsp;<a href="{{ route('admin')}}"> ρυθμίσεις</a>
                                        </p>
                                    </div>

                                 @endif
                               

                        </nav>

                    </div>
                    </form>
                </nav>

            </div>
        </div>


  <div class="columns is-marginless is-centered">
    <div class="column is-10">
      <nav class="card">
        <header class="card-header">
          <p class="card-header-title">
            @php($apousiesDaysCount = (new App\Apousie)->apousiesDaysCount())
            @if($apousiesDaysCount)
            Ημέρες με απουσίες: {{$apousiesDaysCount}}
            @else
            Δεν καταχωρίστηκαν ημέρες απουσιών
            @endif
          </p>
        </header>

        <form name="formProg" id="formProg" role="form" method="POST" action="{{ url('/insertprogram') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-content">
            <nav class="level is-mobile">
              <a class="button level-item" onclick="chkDelApousiesDays(event)" @if(! $apousiesDaysCount) disabled @endif>
                <span class="icon">
                  <i class="fa fa-trash"></i>
                </span>
                <span>Διαγραφή όλων ημερών με απουσίες εκτός από</span>
              </a>
              <input id="daysToKeep" class="input column is-2  has-text-centered" type="text" />
            </nav>
          </div>
        </form>
      </nav>
    </div>
  </div>

</div>

<script>
  const fileInput_k = document.querySelector('#file-kathigites input[type=file]');
  fileInput_k.onchange = () => {
    if (fileInput_k.files.length > 0) {
      document.getElementById("sbmt_kath").disabled = false;
      const fileName = document.querySelector('#file-kathigites .file-name');
      fileName.textContent = fileInput_k.files[0].name;
    }
  }
  const fileInput_m = document.querySelector('#file-mathites input[type=file]');
  fileInput_m.onchange = () => {
    if (fileInput_m.files.length > 0) {
      document.getElementById("sbmt_math").disabled = false;
      const fileName = document.querySelector('#file-mathites .file-name');
      fileName.textContent = fileInput_m.files[0].name;
    }
  }
  const fileInput_p = document.querySelector('#file-program input[type=file]');
  fileInput_p.onchange = () => {
    if (fileInput_p.files.length > 0) {
      document.getElementById("sbmt_prog").disabled = false;
      const fileName = document.querySelector('#file-program .file-name');
      fileName.textContent = fileInput_p.files[0].name;
    }
  }
  const fileInput_a = document.querySelector('#file-myschApou input[type=file]');
  fileInput_a.onchange = () => {
    if (fileInput_a.files.length > 0) {
      document.getElementById("sbmt_myschAp").disabled = false;
      const fileName = document.querySelector('#file-myschApou .file-name');
      fileName.textContent = fileInput_a.files[0].name;
    }
  }

  const fileInput = document.querySelector('#file-xls input[type=file]');
  fileInput.onchange = () => {
    if (fileInput.files.length > 0) {
      document.getElementById("sbmt_xls").disabled = false;
      const fileName = document.querySelector('#file-xls .file-name');
      fileName.textContent = fileInput.files[0].name;
    }
  }

  function chkDelKathigites(e) {
    e.preventDefault();
    @if($kathigitesCount)
    if (confirm('Θέλετε να διαγραφούν {{$kathigitesCount}} καθηγητές;')) window.location.href = "{{ url('/delkath') }}";
    @endif
  }

  function chkDelStudents(e) {
    e.preventDefault();
    @if($studentsCount)
    if (confirm('Θέλετε να διαγραφούν {{$studentsCount}} μαθητές;')) window.location.href = "{{ url('/delmath') }}";
    @endif
  }

  function chkDelProgram(e) {
    e.preventDefault();
    @if($programCount)
    if (confirm('Θέλετε να διαγραφεί το πρόγραμμα;')) window.location.href = "{{ url('/delprog') }}";
    @endif
  }

  function chkDelApousiesDays(e) {
    e.preventDefault();
    @if($apousiesDaysCount)
    var keepDays = document.getElementById('daysToKeep').value
    if (keepDays) {
      msg = 'Θέλετε να διαγραφούν όλες οι ημέρες με απουσίες εκτός από τις τελευταίες ' + keepDays + ';'
    } else {
      msg = 'Θέλετε να διαγραφούν όλες οι ημέρες με απουσίες;'
    }
    if (confirm(msg)) window.location.href = "{{ url('/delapou') }}/" + keepDays
    @endif
  }

  function chkCustomDateFormat(){
    const element = document.getElementById('setCustomDate')
    if(element.value){
      if( ! element.value.match(/^(\d{2})\/(\d{2})\/(\d{2})$/)){
        alert('Η ημερομηνία πρέπει να έχει τη μορφή "ηη/μμ/εε"')
        element.focus();
        return false
      }
    }
  }

</script>
@endsection
