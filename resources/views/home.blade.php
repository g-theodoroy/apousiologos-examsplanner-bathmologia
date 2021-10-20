@extends('layouts.app')

@php($isAdmin = Auth::user()->role->role == 'Διαχειριστής')

    @section('content')

        <div class="container">
            <div class="columns is-marginless is-centered">
                <div class="column is-10">
                    <nav class="card">
                        <form name="formApousies" id="formApousies" role="form" method="POST"
                            action="{{ url('/home', $selectedTmima) }}" onsubmit="event.preventDefault();formValidateDate()">
                            {{ csrf_field() }}
                            <header class="card-header">
                                <div class="level is-mobile">
                                    @php($now = Carbon\Carbon::Now()->format('d/m/y'))
                                        @if ((!$isAdmin && $setCustomDate) || (!$isAdmin && ! \App\Config::getConfigValueOf('pastDaysInsertApousies')))
                                            <p
                                                class="card-header-title level-item column is-narrow has-text-centered {{ $setCustomDate ? 'has-background-warning' : '' }}">
                                                {{ $setCustomDate ? $setCustomDate : $now }}
                                                <input class="input " id="date" name="date" type="hidden"
                                                    value="{{ $setCustomDate ? $setCustomDate : $now }}" size="3" />
                                            </p>
                                        @endif
                                        <p class="box card-header-title level-item column">
                                            @foreach ($anatheseis as $anathesi)
                                                <a
                                                    href="{{ url('/home', $anathesi) }}/{{ !$isAdmin && $setCustomDate ? Carbon\Carbon::createFromFormat('!d/m/y', $setCustomDate)->format('Ymd') : Carbon\Carbon::createFromFormat('!d/m/y', $date)->format('Ymd') }}">{{ $anathesi }}</a>&nbsp;
                                            @endforeach
                                            @if ($isAdmin)
                                                <a
                                                    href="{{ url('/home/0') }}/{{ Carbon\Carbon::createFromFormat('!d/m/y', $date)->format('Ymd') }}"><span
                                                        class="icon"><i class="fa fa-times"></i></span></a>&nbsp;
                                            @endif
                                        </p>
                                    </div>
                                </header>
                                @if ($isAdmin || ( ! $setCustomDate && \App\Config::getConfigValueOf('pastDaysInsertApousies')))
                                    <div class="card-content">
                                        <div class="columns is-mobile is-centered">
                                            <div class="column is-narrow level">
                                                <div class="field has-addons level has-text-centered">
                                                    <p class="control">
                                                        <a class="button" onclick="calculateDate('-')">
                                                            <span class="icon" title="Προηγούμενη ημέρα"><i
                                                                    class="fa fa-angle-left"></i></span>
                                                        </a>
                                                    </p>
                                                    <p class="control">
                                                        <a class="button is-static">
                                                            {{ Carbon\Carbon::createFromFormat('!d/m/y', $date)->dayName }}
                                                        </a>
                                                    </p>
                                                    <p class="control">
                                                        <input class="input level-item has-text-centered" id="date" name="date"
                                                            type="text" value="{{ $date }}" size="7" placeholder="ηη/μμ/εε" />
                                                    </p>
                                                    @if ($now !== $date)
                                                        <p class="control">
                                                            <a class="button" href="{{ url('/home', $selectedTmima) }}">
                                                                <span class="icon" title="Σήμερα"><i class="fa fa-home"></i></span>
                                                            </a>
                                                        </p>
                                                    @endif
                                                    <p class="control">
                                                        <a class="button" onclick="calculateDate('+')">
                                                            <span class="icon" title="Επόμενη ημέρα"><i
                                                                    class="fa fa-angle-right"></i></span>
                                                        </a>
                                                    </p>
                                                    @if($isAdmin)
                                                        <p class="control">
                                                            <a class="button" href="javascript:exportXls()">
                                                                <span class="icon" title="Εξαγωγή xls"><i class="fa fa-download"></i></span>
                                                            </a>
                                                        </p>
                                                    @endif
                                                    <p class="control">
                                                        <a id="changeDate" class="button" href="{{ url('/home', $selectedTmima) }}"
                                                            onclick="return changeDate(this)"><span class="icon" title="Βρες την ημέρα"><i
                                                                    class="fa fa-search"></i></span></a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($isAdmin && $sumApousies)
                                    <div class="card-content">
                                        <div class="columns is-centered">
                                            <div class="column is-narrow">
                                                <div class="table-container">
                                                    <table class="table is-narrow">
                                                        <thead>
                                                            <tr>
                                                                <th class='has-text-centered' style="background-color: #f2f2f2;">
                                                                    Τάξη</th>
                                                                @for ($i = 1; $i < 8; $i++)
                                                                    <th style="background-color: #f2f2f2;"
                                                                        title="Αρ. μαθητών με {{ $i }} {{ $i == 1 ? 'απουσία' : 'απουσίες' }}">
                                                                        {{ $i }}
                                                                    </th>
                                                                @endfor
                                                                <th style="background-color: #f2f2f2;">Σύνολο</th>
                                                                @for ($i = 2; $i < 7; $i++)
                                                                    <th style="background-color: #f2f2f2;"
                                                                        title="Αρ. μαθητών με {{ $i }} {{ $i == 1 ? 'απουσία' : 'απουσίες' }} και πάνω">
                                                                        >={{ $i }}</th>
                                                                @endfor
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($taxeis as $taxi)
                                                                <tr>
                                                                    <th class='has-text-centered'>{{ $taxi }}</th>
                                                                    @foreach ($sumApousies[$taxi]['equal'] as $key => $value)
                                                                        <th class='has-text-centered'>{{ $value ? $value : '' }}
                                                                        </th>
                                                                    @endforeach
                                                                    @foreach ($sumApousies[$taxi]['over'] as $key => $value)
                                                                        <th class='has-text-centered'>{{ $value ? $value : '' }}
                                                                        </th>
                                                                    @endforeach
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        @if (!$selectedTmima)
                                                            <tfoot>
                                                                <tr>
                                                                    <th style="background-color: #f2f2f2;">Σύνολα</th>
                                                                    @foreach ($sumApousies['sums']['equal'] as $key => $value)
                                                                        <th class='has-text-centered'
                                                                            style="background-color: #f2f2f2;">
                                                                            {{ $value ? $value : '' }}
                                                                        </th>
                                                                    @endforeach
                                                                    @foreach ($sumApousies['sums']['over'] as $key => $value)
                                                                        <th class='has-text-centered'
                                                                            style="background-color: #f2f2f2;">
                                                                            {{ $value ? $value : '' }}
                                                                        </th>
                                                                    @endforeach
                                                                </tr>
                                                            </tfoot>
                                                        @endif
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="card-content">
                                    <div class="columns is-centered">
                                        <div class="column is-narrow">
                                            <div class="level has-text-centered ">
                                                @if (($selectedTmima && count($arrStudents)) || ($isAdmin && count($arrStudents)))
                                                    <p class="card-header-title level-item">
                                                        {{ $selectedTmima ? $selectedTmima : 'Όλα τα τμήματα' }}
                                                    </p>

                                                    @if ((!$isAdmin && !$activeHour && !$hoursUnlocked) || ! App\Anathesi::countMathimata())
                                                        <p class="card-header-title level-item">
                                                            Εκτός ωραρίου
                                                        </p>
                                                    @endif
                                                    @if (($isAdmin || $activeHour != 0 || ($hoursUnlocked && $allowTeachersSaveAtNotActiveHour )) && App\Anathesi::countMathimata())
                                                        @if (!$isWeekend || ($isWeekend && $allowWeekends))
                                                            <a class="button level-item" onclick="formValidateDate()">
                                                                <span class="icon">
                                                                    <i class="fa fa-save"></i>
                                                                </span>
                                                                <span>Αποθήκευση</span>
                                                            </a>
                                                        @endif
                                                        @if (($letTeachersUnlockHours && $activeHour > 0 && !$isAdmin && !$allowTeachersSaveAtNotActiveHour) && App\Anathesi::countMathimata())
                                                            <a class="button level-item" onclick="unlockChks()">
                                                                <span class="icon">
                                                                    <i class="fa fa-key"></i>
                                                                </span>
                                                                <span>Ξεκλείδωμα</span>
                                                            </a>
                                                        @endif
                                                    @endif
                                            </div>

                                            <div class="table-container">
                                                <table class="table is-narrow">
                                                    <thead>
                                                        <tr>
                                                            @if (!$selectedTmima)
                                                                <th>{{ !$selectedTmima && count($arrStudents) ? count($arrStudents) : '' }}
                                                                </th>
                                                            @endif
                                                            <th>{{ !$selectedTmima ? 'Τμ' : '' }}</th>
                                                            <th>Ονοματεπώνυμο</th>
                                                            <th @if (!$isAdmin && !$activeHour && !$hoursUnlocked)
                                                                style="display:none;" @endif><span
                                                                    class="icon">
                                                                    <i class="fa fa-calculator"></i>
                                                                </span></th>
                                                            @for ($i = 1; $i < $totalHours + 1; $i++)
                                                                @if ($activeHour > 0)
                                                                    <th @if ($i == $activeHour)
                                                                        style="background-color: #f2f2f2;"
                                                                @endif
                                                                @if (!$isAdmin && !$showFutureHours && $i > $activeHour)
                                                                    style="display:none;" @endif >{{ $i }}η
                                                                </th>
                                                            @elseif(!$isAdmin && $activeHour==0 && ! $hoursUnlocked)
                                                                <th style="display:none;">{{ $i }}η</th>
                                                            @else
                                                                <th @if ($i % 2 != 0)
                                                                    style="background-color: #f2f2f2;"
                                                            @endif >{{ $i }}η</th>
                                                            @endif
                                                            @endfor
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($arrStudents as $student)
                                                            <tr>
                                                                @if (!$selectedTmima)
                                                                    <td class="has-text-centered" title="{{ $student['tmimata'] }}">
                                                                        {{ $loop->index + 1 }}
                                                                    </td>
                                                                @endif
                                                                <td class="has-text-centered" title="{{ $student['tmimata'] }}">
                                                                    {{ $selectedTmima ? $loop->index + 1 : $student['tmima'] }}
                                                                </td>
                                                                <td>
                                                                    <input class="input" id="ap{{ $student['id'] }}"
                                                                        name="ap{{ $student['id'] }}"
                                                                        value="{{ $student['apousies'] }}" type="hidden" size="5" />
                                                                    {{ $student['eponimo'] }} {{ $student['onoma'] }}
                                                                </td>
                                                                <th class="has-text-centered" @if (!$isAdmin && !$activeHour && !$hoursUnlocked)
                                                                    style="display:none;"
                                                        @endif
                                                        >{{ array_sum(preg_split('//', $student['apousies'])) > 0 ? array_sum(preg_split('//', $student['apousies'])) : '' }}
                                                        </th>
                                                        @for ($i = 1; $i < $totalHours + 1; $i++)
                                                            @if ($activeHour > 0)
                                                                <th @if ($i == $activeHour)
                                                                    style="background-color: #f2f2f2;"
                                                            @endif
                                                            @if (!$isAdmin && !$showFutureHours && $i > $activeHour)
                                                                style="display:none;" @endif >
                                                        @elseif(!$isAdmin && $activeHour==0 && ! $hoursUnlocked)
                                                            <th style="display:none;">
                                                            @else
                                                            <th @if ($i % 2 != 0)
                                                                style="background-color: #f2f2f2;"
                                                        @endif >
                                                        @endif
                                                        <input type="checkbox"
                                                            onclick="chkClicked(this.checked,{{ $student['id'] }},{{ $i - 1 }})"
                                                            @if ($student['apousies'][$i - 1] ?? null)
                                                        checked @endif
                                                        @if ($i != $activeHour && !$isAdmin && !$allowTeachersSaveAtNotActiveHour)
                                                            disabled @endif
                                                        @if ($hoursUnlocked && $i != $activeHour && !$allowTeachersSaveAtNotActiveHour && !$isAdmin)
                                                            disabled @endif >
                                                        </th>
                                                        @endfor

                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="level has-text-centered ">
                                                <p class="card-header-title level-item">
                                                    {{ $selectedTmima ? $selectedTmima : 'Όλα τα τμήματα' }}
                                                    @if ($isAdmin)
                                                        <br>{{ $date }}
                                                    @endif
                                                </p>
                                                @if ((!$isAdmin && !$activeHour && !$hoursUnlocked) || ! App\Anathesi::countMathimata())
                                                    <p class="card-header-title level-item">
                                                        Εκτός ωραρίου
                                                    </p>
                                                @endif
                                                @if (($isAdmin || $activeHour != 0 || ($hoursUnlocked && $allowTeachersSaveAtNotActiveHour )) && App\Anathesi::countMathimata())
                                                    @if (!$isWeekend || ($isWeekend && $allowWeekends))
                                                        <a class="button level-item" onclick="formValidateDate()">
                                                            <span class="icon">
                                                                <i class="fa fa-save"></i>
                                                            </span>
                                                            <span>Αποθήκευση</span>
                                                        </a>
                                                    @endif
                                                    @if (($letTeachersUnlockHours && $activeHour > 0 && !$isAdmin && !$allowTeachersSaveAtNotActiveHour) && App\Anathesi::countMathimata())
                                                        <a class="button level-item" onclick="unlockChks()">
                                                            <span class="icon">
                                                                <i class="fa fa-key"></i>
                                                            </span>
                                                            <span>Ξεκλείδωμα</span>
                                                        </a>
                                                    @endif
                                                @endif
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
                                                        Ωραία!<br>
                                                        Δεν λείπει κανείς!<br><br>
                                                        <i class="fa fa-smile-o" aria-hidden="true"></i><br>
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

            <script src="{{ asset('js/jquery.min.js') }}"></script>
            <script>
                String.prototype.replaceAt = function(index, replacement) {
                    return this.substr(0, index) + replacement + this.substr(index + replacement.length);
                }

                function chkClicked(checked, am, position) {
                    apval = document.getElementById('ap' + am).value
                    if (checked == true) {
                        if (!apval) apval = "{{ str_repeat('0', $totalHours) }}"
                        apval = apval.replaceAt(position, "1")
                    } else {
                        apval = apval.replaceAt(position, "0")
                        if (apval == "{{ str_repeat('0', $totalHours) }}") apval = ''
                    }
                    document.getElementById('ap' + am).value = apval
                }

                function unlockChks() {
                    if (!confirm("Είστε σίγουροι ότι θλελετε να ξεκλειδώσετε τα κουτιά;")) return false
                    var checkboxes = document.querySelectorAll('input[type="checkbox"]')
                    for (var checkbox of checkboxes) {
                        checkbox.disabled = false
                    }
                }

                function changeDate(element) {
                    var mydate = document.getElementById('date').value
                    if(! chkDateFormat(mydate)) return false
                    mydate = "20" + mydate.substring(6, 8) + mydate.substring(3, 5) + mydate.substring(0, 2)
                    element.href = "{{ url('/home', $selectedTmima) }}" + '/' + mydate
                    return true;
                }

                function formValidateDate() {
                    var dateToCommit = document.getElementById('date').value
                    if(! chkDateFormat(dateToCommit)) return
                    if (dateToCommit !== '{{ $date }}') {
                        document.getElementById('changeDate').click()
                    } else {
                        document.getElementById('formApousies').submit()
                    }
                }

                function calculateDate(to) {
                    mydateStr = document.getElementById('date').value
                    if(! chkDateFormat(mydateStr)) return
                    mydateStr = mydateStr.substring(0, 2) + '/' + mydateStr.substring(3, 5) + '/' + "20" + mydateStr.substring(6, 8)
                    mydate = parseDate(mydateStr)
                    if (to == '+') {
                        newdate = new Date(mydate.getTime() + 86400000); // + 1 day in ms
                        @if (!$isAdmin)
                            var todayDate = new Date();
                            if(newdate > todayDate ){
                                newdate = todayDate
                                alert('Δεν επιτρέπεται η πλοήγηση σε μελλοντικές ημερομηνίες')
                                return
                            } 
                        @endif
                    } else {
                        newdate = new Date(mydate.getTime() - 86400000); // + 1 day in ms
                        @if (!$isAdmin &&  \App\Config::getConfigValueOf('pastDaysInsertApousies'))
                            var daysBack = {{ \App\Config::getConfigValueOf('pastDaysInsertApousies') }}
                            var dateOffset = (24*60*60*1000) * daysBack; //5 days
                            var chkDate = new Date();
                            chkDate.setTime(chkDate.getTime() - dateOffset);
                            if(newdate < chkDate ) {
                                newdate = mydate
                                alert('Δεν επιτρέπεται η πλοήγηση σε προγενέστερες ημερομηνίες')
                                return
                            }
                        @endif
                    }
                    dateToGo = [
                        newdate.getFullYear(),
                        ('0' + (newdate.getMonth() + 1)).slice(-2),
                        ('0' + newdate.getDate()).slice(-2)
                    ].join('');
                    window.location.href = "{{ url('/home', $selectedTmima) }}" + '/' + dateToGo
                }

                function parseDate(input, format) {
                    format = format || 'dd/mm/yyyy'; // default format
                    var parts = input.match(/(\d+)/g),
                        i = 0,
                        fmt = {};
                    // extract date-part indexes from the format
                    format.replace(/(yyyy|dd|mm)/g, function(part) {
                        fmt[part] = i++;
                    });

                    return new Date(parts[fmt['yyyy']], parts[fmt['mm']] - 1, parts[fmt['dd']]);
                }

                function exportXls(){
                    var fordate = document.getElementById('date').value
                    if(! chkDateFormat(fordate)) return
                    window.location.href = "{{ route('apouxls') }}" + "?apoDate=" + fordate + "&eosDate=" + fordate
                }

                function chkDateFormat(date){
                    if(! date){
                        alert('Συμπληρώστε την Ημερομηνία' )
                        document.getElementById('date').focus();
                        return false
                    }
                    if( ! date.match(/^(\d{2})\/(\d{2})\/(\d{2})$/)){
                        alert('Η ημερομηνία πρέπει να έχει τη μορφή "ηη/μμ/εε"')
                        document.getElementById('date').focus();
                        return false
                    }
                    return true
                }

            </script>

        @endsection
