@extends('layouts.app')

@php($isAdmin = Auth::user()->role_description() == 'Διαχειριστής')

    @section('content')

        <div class="container">
            <div class="columns is-marginless is-centered">
                <div class="column is-10">
                    <nav class="card">
                        <form name="frm" id="frm" role="form" method="POST"
                            action="{{ url('/grades', $selectedAnathesiId) }}" >
                            {{ csrf_field() }}
                            <header class="card-header">
                                <div class="level is-mobile">
                                         <p class="box card-header-title level-item column">
                                             @php($mathima = '')
                                            @foreach ($anatheseis as $anathesi)
                                                @if($anathesi->mathima)
                                                    {{ $anathesi->mathima !== $mathima ? $anathesi->mathima . ': ' : ''}}
                                                    @php($mathima = $anathesi->mathima)
                                                    <a href="{{ url('grades', $anathesi->id) }}">{{ $anathesi->tmima }}</a>&nbsp;
                                                @endif
                                            @endforeach
                                            @if ($isAdmin)
                                                <a
                                                    href="{{ url('grades/0') }}"><span
                                                        class="icon"><i class="fa fa-times"></i></span></a>&nbsp;
                                            @endif
                                        </p>
                                    </div>
                                </header>
                                    <div class="card-content">
                                        <div class="columns is-mobile is-centered">
                                            <div class="column is-narrow level">
                                                <div class="has-text-centered has-text-weight-bold is-size-4">
                                                    {{App\Period::find(App\Config::getConfigValueOf('activeGradePeriod'))->period}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                <div class="card-content">
                                    <div class="columns is-centered">
                                        <div class="column is-narrow">
                                            <div class="level has-text-centered ">
                                                @if (($selectedTmima && count($arrStudents)) || ($isAdmin && count($arrStudents)))
                                                    <p class="card-header-title level-item">
                                                        {{ $selectedTmima }} -> {{ $selectedMathima}}
                                                    </p>


                                                            <a class="button level-item" onclick="formValidate()">
                                                                <span class="icon">
                                                                    <i class="fa fa-save"></i>
                                                                </span>
                                                                <span>Αποθήκευση</span>
                                                            </a>
                                            </div>

                                                <div class="columns is-centered">
                                                <div class="column is-narrow">
                                                <div class="table-container">
                                                <table class="table is-narrow is-centered">
                                                    <thead>
                                                        <tr>
                                                            <th>&nbsp;</th>
                                                            <th>Ονοματεπώνυμο</th>
                                                            @if(App\Config::getConfigValueOf('showOtherGrades'))
                                                            <th>&nbsp;</th>
                                                            @endif
                                                            @foreach (App\Period::all() as $period)
                                                                @if($period->id <= App\Config::getConfigValueOf('activeGradePeriod'))
                                                                    <th class="has-text-centered">{{ mb_substr($period->period, 0 , 6 ) }}</th>
                                                                @endif
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($arrStudents as $student)
                                                            <tr>
                                                                <td class="has-text-centered" title="{{ $student['tmimata'] }}">
                                                                    {{ $selectedTmima ? $loop->index + 1 : $student['tmima'] }}
                                                                </td>
                                                                <td>
                                                                    {{ $student['eponimo'] }} {{ $student['onoma'] }}
                                                                </td>
                                                                @if(App\Config::getConfigValueOf('showOtherGrades'))
                                                                <td>
                                                                    @if( $gradesPeriodLessons[$student['id']] ?? null )
                                                                        <a href="javascript:showModal({{$student['id']}})" title="Βαθμοί σε άλλα μαθήματα"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                                                    @endif
                                                                </td>
                                                                @endif
                                                                @foreach (App\Period::all() as $period)
                                                                    @if($period->id <= App\Config::getConfigValueOf('activeGradePeriod'))
                                                                        @if($period->id == App\Config::getConfigValueOf('activeGradePeriod'))
                                                                            <td style="width: 70px;" >
                                                                                <input class="input has-text-centered is-small" id="b{{ $student['id'] }}"
                                                                                    name="b{{ $student['id'] }}"
                                                                                    value="{{ $student['grade'] }}" type="text"  />
                                                                            </td>
                                                                        @else
                                                                            <td class="has-text-centered">{{ $gradesStudentsPeriod[$student['id']][$period->id] ?? null }}</td>
                                                                        @endif
                                                                    @endif
                                                                @endforeach
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            </div>
                                            </div>

                                            <div class="level has-text-centered ">
                                                <p class="card-header-title level-item">
                                                        {{ $selectedTmima }} -> {{ $selectedMathima}}
                                                </p>
                                                @if (!$isAdmin && !$activeHour && !$hoursUnlocked)
                                                    <p class="card-header-title level-item">
                                                        Εκτός ωραρίου
                                                    </p>
                                                @endif
                                                        <a class="button level-item" onclick="formValidate()">
                                                            <span class="icon">
                                                                <i class="fa fa-save"></i>
                                                            </span>
                                                            <span>Αποθήκευση</span>
                                                        </a>

                                            </div>

                                        @else
                                            <p class="title">
                                                <br>
                                                @if ($isAdmin)
                                                    @if (!App\User::get_num_of_kathigites())
                                                        <a href="{{ route('admin') }}">Πρέπει να εισάγετε καθηγητές</a><br>
                                                        <br>
                                                        <i class="fa fa-frown-o" aria-hidden="true"></i><br>
                                                    @elseif(! App\Student::get_num_of_students())
                                                        <a href="{{ route('admin') }}">Πρέπει να εισάγετε μαθητές</a><br>
                                                        <br>
                                                        <i class="fa fa-frown-o" aria-hidden="true"></i><br>
                                                    @else
                                                        Επιλέξτε ένα τμήμα
                                                    @endif
                                                @else
                                                    @if (!App\User::get_num_of_kathigites() || !App\Student::get_num_of_students())
                                                        Δυστυχώς υπολείπονται ρυθμίσεις και<br>
                                                        η εφαρμογή δεν είναι λειτουργική
                                                        <br>
                                                        <br>
                                                        <i class="fa fa-frown-o" aria-hidden="true"></i><br>
                                                    @else
                                                        Επιλέξτε ένα τμήμα
                                                    @endif
                                                @endif
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </nav>
                    </div>
                </div>
            </div>

            @foreach($gradesPeriodLessons as $studentId => $data)
            <div id="modal{{ $studentId }}" class="modal">
                <div class="modal-background"></div>
                    <div class="modal-card">
                        <header class="modal-card-head">
                            <p id="modalTitle" class="modal-card-title">{{ $data['name']}}</p>
                            <button class="delete" aria-label="close" onclick="$('#modal{{ $studentId }}').removeClass('is-active')"></button>
                        </header>
                        <section class="modal-card-body">
                            <table class="table is-fullwidth">
                                <tr>
                                    <th>Μάθημα</th>
                                    @foreach (App\Period::all() as $period)
                                        @if($period->id <= App\Config::getConfigValueOf('activeGradePeriod'))
                                            <th class="has-text-centered">{{ mb_substr($period->period, 0 , 6 ) }}</th>
                                        @endif
                                    @endforeach
                                </tr>
                                @foreach ($mathimata as $mathima)
                                @if($data[$mathima] ?? null)
                                <tr>
                                    @if($selectedMathima == $mathima)
                                        <td><b>{{ $mathima }}</b></td>
                                    @else
                                        <td>{{ $mathima }}</td>
                                    @endif
                                    @foreach (App\Period::all() as $period)
                                        @if($period->id <= App\Config::getConfigValueOf('activeGradePeriod'))
                                            <td class="has-text-centered"> {{$data[$mathima][$period->id] ?? null }}</td>
                                        @endif
                                    @endforeach
                                </tr>
                                @endif
                                @endforeach
                        </table>

                        </section>
                        <footer class="modal-card-foot">
                            <button class="button" onclick="$('#modal{{ $studentId }}').removeClass('is-active')">Εντάξει</button>
                        </footer>
                    </div>
                </div>
            </div>
            @endforeach


        <div id="errorModal" class="modal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p id="errorModalTitle" class="modal-card-title">Έλεγχος καταχώρισης βαθμών</p>
                    <button class="delete" aria-label="close" onclick="$('#errorModal').removeClass('is-active')"></button>
                </header>
                <section class="modal-card-body">
                    <div id="errorModalContent" class="column is-offset-1"></div>
                </section>
                <footer class="modal-card-foot">
                    <button class="button" onclick="$('#errorModal').removeClass('is-active')">Εντάξει</button>
                </footer>
            </div>
        </div>


            <script src="{{ asset('js/jquery.min.js') }}"></script>
            <script>
                function formValidate(){
                    // ενεργοποιώ τα disabled πεδία
                    for(let field of document.forms['frm'].elements) {
                        if (field.name && field.name.substr(0,1) == 'b'){
                            if(field.value){
                                @if(App\Config::getConfigValueOf('activeGradePeriod') < 3)
                                 if( field.value != 'Α' && field.value != 'Δ' && parseInt(field.value) != field.value){
                                    $('#' + field.name).addClass("is-danger")
                                    $('#errorModalContent').html('<div>Πληκτρολογείστε μόνο:</div><div class="column is-offset-1">&#8226; ακέραιους αριθμούς</div><div class="column is-offset-1">&#8226; Α => "Απαλλαγή"</div><div class="column is-offset-1">&#8226; Δ => "Δεν έχω άποψη"</div>')
                                    $('#errorModal').addClass("is-active")
                                    return
                                }
                                @else
                                    if(! /^(\d+)?([,.]?\d{0,1})?$/.test(field.value) ){
                                    $('#' + field.name).addClass("is-danger")
                                    $('#errorModalContent').html('<div>Πληκτρολογείστε μόνο αριθμούς:</div><div class="column is-offset-1">&#8226; ακέραιους</div><div class="column is-offset-1">&#8226; με ένα δεκαδικό ψηφίο</div>')
                                    $('#errorModal').addClass("is-active")
                                    return
                                    }
                                @endif
                                if(parseFloat(field.value) && (parseFloat(field.value.replace(',', '.')) > 20 || parseFloat(field.value.replace(',', '.')) < 0)){
                                    $('#' + field.name).addClass("is-danger")
                                    $('#errorModalContent').html('Η τιμή ' + field.value + ' είναι εκτός ορίων της βαθμολογικής κλίμακας 0 - 20')
                                    $('#errorModal').addClass("is-active")
                                    return
                                }
                            }
                        }
                    }
                    // έλεγχος αν από λάθος πληκτρολόγηση καταχωρίζονται βαθμοί 
                    // κάτω από τη βάση 'gradeBaseAlert' και ειδοποίηση του χρήστη
                    @if(App\Config::getConfigValueOf('gradeBaseAlert'))
                        const gradeBase = parseFloat({{ App\Config::getConfigValueOf('gradeBaseAlert') }})
                        var gradeAlert = false
                        for(let field of document.forms['frm'].elements) {
                            if (field.name && field.name.substr(0,1) == 'b'){
                                if(field.value){
                                    if(parseFloat(field.value.replace(',', '.')) < gradeBase){
                                        gradeAlert = true
                                        break
                                    }
                                    
                                }
                            }
                        }
                        if(gradeAlert){
                            if( ! confirm('Καταχωρίζετε βαθμούς κάτω από τη "βάση" του ' + gradeBase + '.\n\nΘέλετε ωστόσο να προχωρήσετε;')) return
                        }
                    @endif
                    $('#frm').submit()
                }
                function showModal(id){
                    $('#modal' + id ).addClass('is-active')
                }
            </script>

        @endsection
