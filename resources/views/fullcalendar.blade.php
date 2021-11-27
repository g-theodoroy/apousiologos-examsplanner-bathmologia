@extends('layouts.app')

@php($isAdmin = Auth::user()->role->role == 'Διαχειριστής')

    @section('content')

        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <link href="{{ asset('css/fullcalendar.min.css') }}" rel="stylesheet" />
        <script src="{{ asset('js/fullcalendar.min.js') }}"></script>
        <script src="{{ asset('js/fullcalendar.el.js') }}"></script>

        <div class="container">
            <div class="columns is-marginless is-centered">
                <div class="column is-12">
                    <nav class=" card">
                        <header class="card-header">
                            <p class="card-header-title">
                                <span>Προγραμματισμός διαγωνισμάτων</span>
                                <span class="icon"><i class="fa fa-calendar"></i></span>
                            </p>
                            <p class="response card-header-title "></p>
                            @admin
                                <a class="button" href="javascript:void(0)" id="print">
                                    <span class="icon"><i class="fa fa-download"></i></span>
                                    <span>Εξαγωγή</span>
                                </a>
                                &nbsp;
                            @endadmin
                            <a class="button" href="javascript:void(0)" id="myEvents">
                                <span class="icon"><i class="fa fa-book"></i></span>
                                <span>Τα διαγωνίσματά μου</span>
                            </a>
                        </header>
                        <div class="card-content">
                            <div id='calendar'></div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>

        <div id="calendarModal" class="modal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p id="modalTitle" class="modal-card-title">Δήλωση διαγωνίσματος</p>
                    <button id="closeCalendarModal" class="delete" aria-label="close"></button>
                </header>
                <section class="modal-card-body">
                    <p id='showError' class="help is-danger"></p>

                    <!-- Content ... -->
                    <form id="calendarForm" class="form-horizontal">
                        @csrf

                        <fieldset>
                            <input id="start" name="start" type="hidden" placeholder="start" class="input" />
                            <input id="end" name="end" type="hidden" placeholder="end" class="input" />
                            <input id="week" name="week" type="hidden" placeholder="week" class="input" />
                            <input id="user_id" name="user_id" type="hidden" placeholder="user_id"
                                value="{{ Auth::user()->id }}" class="input" />
                            <div class="field">
                                <p id='showError' class="help is-danger"></p>
                            </div>

                            <div class="level is-mobile">
                                <div class="field level-item is-half ">
                                    <label class="label" for="tmima1">Τμήμα&nbsp;</label>
                                </div>

                                <!-- Text input-->
                                <div class="field level-item is-half">
                                    <label class="label" for="tmima2">Τμήμα&nbsp;</label>
                                </div>
                            </div>

                            <div class="level is-mobile">
                                <div class="field level-item is-half ">
                                    <div class="select">
                                        <select id="tmima1" name="tmima1"
                                            onchange="copyValues(this.value)">
                                            <option value=""></option>
                                            <option value="Α1">Α1</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Text input-->
                                <div class="field level-item is-half">
                                    <div class="select">
                                        <select id="tmima2" name="tmima2" disabled>
                                            <option value=""></option>
                                            <option value="Α2">Α2</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="level is-mobile">
                                <p id='tmima1Error' class="help is-danger level-item"></p>
                                <p id='tmima2Error' class="help is-danger level-item"></p>
                            </div>

                            <div class="level is-mobile">
                                <div class="field level-item">
                                    <label class="label" for="mathima">Μάθημα</label>
                                </div>
                            </div>
                            <div class="level is-mobile">
                                <div class="field level-item">
                                   <div class="select" style="position: absolute; max-width: 75% ; text-overflow: ellipsis;">
                                        <select id="mathima" name="mathima" >
                                            <option value=""></option>
                                            @foreach ($mathimata as $mathima)
                                                <option value="{{ $mathima }}">{{ $mathima }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="level">
                                <p id='mathimaError' class="help is-danger level-item"></p>
                            </div>

                        </fieldset>
                    </form>

                </section>
                <footer class="modal-card-foot">
                    <button id="formSubmit" class="button">Αποθήκευση</button>
                    <button id="formReset" class="button">Άκυρο</button>
                </footer>
            </div>
        </div>

        <div id="delModal" class="modal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p id="modalTitle" class="modal-card-title">Διαγραφή διαγωνίσματος</p>
                    <button id="closeCalendarModal" class="delete" aria-label="close" onclick="$('#delModal').removeClass('is-active')"></button>
                </header>
                <section class="modal-card-body">
                    <p>Θα διαγραφεί το διαγώνισμα</p>
                    <p>&nbsp;</p>
                    <p><span id="eventTitle"></span>  <span id="eventDate"></span></p>
                    <p>&nbsp;</p>
                    <p>Να συνεχίσω;</p>

                    <!-- Content ... -->

                </section>
                <footer class="modal-card-foot">
                    <button id="delOk" class="button" delId="" caller="" onclick="doDelete()">Διαγραφή</button>
                    <button id="delNo" class="button" onclick="if($('#delOk').attr('caller')){$('#delOk').attr('caller', ''); $('#eventsModal').addClass('is-active')}; $('#delModal').removeClass('is-active')">Άκυρο </button>
                </footer>
            </div>
        </div>

        <div id="eventsModal" class="modal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p id="eventsModalTitle" class="modal-card-title"></p>
                    <button id="closeCalendarModal" class="delete" aria-label="close" onclick="$('#eventsModal').removeClass('is-active')"></button>
                </header>
                <section class="modal-card-body">
                    <div id="eventsModalContent"></div>
                </section>
                <footer class="modal-card-foot">
                    <button id="delNo" class="button" onclick="$('#eventsModal').removeClass('is-active')">Εντάξει </button>
                </footer>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                SITEURL = "{{ url('/') }}";
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var calendarEl = document.getElementById('calendar');
                calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'el',
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                    left: 'title',
                    center: 'dayGridWeek,listWeek,dayGridMonth,listMonth',
                    right: 'today prev,next'
                    },
                    weekNumbers: false,
                    weekends: false,
                    editable: false,
                    events: SITEURL + "/calendar/data",
                    displayEventTime: true,
                    selectable: true,
                    eventDidMount: function(info) {
                        info.el.title = info.event.title
                        if(calendar.view.type == 'dayGridWeek' || calendar.view.type == 'dayGridMonth'){
                            if (info.event.extendedProps.user_id !== '{{ Auth::user()->id }}') {
                                info.el.style.backgroundColor = "Grey";
                                info.el.style.borderColor = "Grey";
                            }
                            if(info.event.startStr < (new Date).toISOString().split('T')[0]){
                                info.el.style.backgroundColor = "LightBlue";
                                info.el.style.borderColor = "LightBlue";
                            }
                            if (info.event.extendedProps.user_id !== '{{ Auth::user()->id }}' && info.event.startStr < (new Date).toISOString().split('T')[0]){
                                info.el.style.backgroundColor = "LightGrey";
                                info.el.style.borderColor = "LightGrey";
                            }
                        }
                        if(calendar.view.type == 'listWeek' || calendar.view.type == 'listMonth'){
                            if (info.event.extendedProps.user_id !== '{{ Auth::user()->id }}') {
                                var dotEl = info.el.getElementsByClassName('fc-list-event-dot')[0];
                                    if (dotEl) {
                                    dotEl.style.borderColor = 'Grey';
                                }
                            }
                            if(info.event.startStr < (new Date).toISOString().split('T')[0]){
                                var dotEl = info.el.getElementsByClassName('fc-list-event-dot')[0];
                                    if (dotEl) {
                                    dotEl.style.borderColor = 'LightBlue';
                                }
                            }
                            if (info.event.extendedProps.user_id !== '{{ Auth::user()->id }}' && info.event.startStr < (new Date).toISOString().split('T')[0]){
                                var dotEl = info.el.getElementsByClassName('fc-list-event-dot')[0];
                                    if (dotEl) {
                                    dotEl.style.borderColor = 'LightGrey';
                                }
                            }
                        }
                   },
                    select: function(info) {
                        var week = info.startStr.substr(0, 4) + myWeekNumber(info.start)
                        $('#start').val(info.startStr)
                        $('#end').val(info.endStr)
                        $('#week').val(week)
                        $('#modalTitle').html(info.startStr + '<br><br>Δήλωση διαγωνίσματος')
                        $.ajax({
                            url: SITEURL + "/calendar/tmimata",
                            type: "POST",
                            dataType: 'JSON',
                            data: {
                                'start': info.startStr,
                                'week': week
                            },
                            success: function(data) {
                                var options = $("#tmima1");
                                options.empty()
                                options.append(new Option('', ''))
                                $.each(data, function(key, val) {
                                    options.append(new Option(val, val))
                                });
                                options = $("#tmima2");
                                options.empty()
                                options.append(new Option('', ''))
                                $.each(data, function(key, val) {
                                    options.append(new Option(val, val));
                                });
                                $('#calendarModal').addClass("is-active")
                                calendar.unselect();
                           }
                        });
                    },
                    eventDrop: function(info) {
                        var week = info.event.startStr.substr(0, 4) + myWeekNumber(info.event.start)
                        var oldWeek = info.oldEvent.startStr.substr(0, 4) + myWeekNumber(info.oldEvent.start)
                        $.ajax({
                            url: SITEURL + '/fullcalendar/update',
                            type: "POST",
                            dataType: 'JSON',
                            data: {
                                'start': info.event.startStr,
                                'end': info.event.endStr,
                                'week': week,
                                'oldWeek': oldWeek,
                                'id': info.event.id,
                                'tmima1': info.event.extendedProps.tmima1,
                                'tmima2': info.event.extendedProps.tmima2
                            },
                            success: function(response) {
                                displayMessage("Επιτυχημένη ενημέρωση");
                            },
                            error: function (data) {
                                displayMessage("Μη επιτρεπτή ενημέρωση", "has-text-danger");
                                $('#eventsModalContent').html(data.responseJSON.error)
                                $('#eventsModalTitle').html("Μη επιτρεπτή δήλωση διαγωνίσματος")
                                $('#eventsModal').addClass("is-active")
                                //alert(data.responseJSON.error)
                                info.revert()
                            }
                        });
                    },
                    eventClick: function(info) {
                        // Αν ο χρήστης του event δεν είναι ίδιος με τον Auth::user()
                        // δεν προχωράει η διαγραφή. Εξαιρείται ο Διαχειριστής.
                        var isAdmin = {{$isAdmin ? 'true' : 'false'}}
                        if (info.event.extendedProps.user_id !== '{{ Auth::user()->id }}' && !isAdmin)
                            return
                        $('#eventTitle').html(info.event.title)
                        $('#eventDate').html('στις ' + info.event.startStr)
                        $('#delOk').attr('delId', info.event.id)
                        $('#delModal').addClass('is-active')
                    }
                });
                calendar.render();
            });

            function displayMessage(message, myclass = 'has-text-success' ) {
                $(".response").addClass( myclass);
                $(".response").html( message);
                setInterval(function() {
                    $(".response").html("");
                    $(".response").removeClass(myclass);
                }, 2500);
            }

            function myWeekNumber(thisDate) {
                var dt = new Date(thisDate)
                var onejan = new Date(dt.getFullYear(), 0, 2);
                return Math.ceil((((dt - onejan) / 86400000) + onejan.getDay() + 1) / 7);
            }

            $('body').on('click', '#formReset', function() {
                $('#calendarForm').trigger("reset")
                $('#tmima2').prop('disabled', true)
                $('.help').html("")
                $('#calendarModal').removeClass("is-active")
            })

            $('body').on('click', '#closeCalendarModal', function() {
                $('#calendarForm').trigger("reset")
                $('#tmima2').prop('disabled', true)
                $('.help').html("")
                $('#calendarModal').removeClass("is-active");
            })

            $('body').on('click', '#formSubmit', function() {


                if (!$('#tmima1').val()) {
                    $('#tmima1Error').html("Επιλέξτε το τμήμα")
                    $('#tmima1').focus()
                    return
                } else {
                    $('#tmima1Error').html("")
                }
                if (!$('#mathima').val()) {
                    $('#mathimaError').html("Επιλέξτε το μάθημα")
                    $('#mathima').focus()
                    return
                } else {
                    $('#tmima1Error').html("")
                }

                $('#formSubmit').prop("disabled", true);
                $.ajax({
                    data: $('#calendarForm').serialize(),
                    url: "{{ route('calendar.create') }}",
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        $('#calendarForm').trigger("reset")
                        $('#tmima2').prop('disabled', true)
                        $('.help').html("")
                        $('#calendarModal').removeClass("is-active")
                        $('#formSubmit').removeAttr('disabled');
                        displayMessage("Επιτυχημένη καταχώριση");
                        calendar.refetchEvents()
                    },
                    error: function(data) {
                        console.log('Error:', data)
                        $('#showError').html(data.responseJSON.message)
                        $('#formSubmit').removeAttr('disabled');
                    }
                })
            })

            function copyValues(selval){
                if(selval){
                    $('#tmima2').empty()
                    $('#tmima1 option').clone().appendTo($('#tmima2'));
                    $('#tmima2').prop( 'disabled', false )
                    .find('[value="'+ selval +'"]')
                    .remove()
                }else{
                    $('#tmima2').prop( 'disabled', true )
                    .val('')
                }
            }
            function doDelete(){
                 var id = $('#delOk').attr('delId')
                 $.ajax({
                    type: "POST",
                    url: SITEURL + '/fullcalendar/delete',
                    dataType: 'JSON',
                    data: {
                    'id': id
                    },
                    success: function(response) {
                        if (parseInt(response) > 0) {
                            if($('#delOk').attr('caller')){
                                $('#delOk').attr('caller','')
                                $('#myEvents').trigger( "click" )
                                $('#delModal').removeClass("is-active")
                            }
                            $('#delModal').removeClass("is-active")
                            calendar.refetchEvents();
                            displayMessage("Επιτυχημένη διαγραφή");
                         }
                    }
                });
            }

            $('body').on('click', '#print', function() {
                curStart = calendar.view.activeStart.getFullYear() + '-' + String('0' + (calendar.view.activeStart.getMonth()+1)).slice(-2) + '-' + String('0' + (calendar.view.activeStart.getDate())).slice(-2)
                curEnd = calendar.view.activeEnd.getFullYear() + '-' + String('0' + (calendar.view.activeEnd.getMonth()+1)).slice(-2) + '-' + String('0' + (calendar.view.activeEnd.getDate())).slice(-2)
                window.location.href = SITEURL + '/calendar/export?start=' + curStart + '&end=' + curEnd 
            })

            $('body').on('click', '#myEvents', function() {
                $.ajax({
                    url: "{{ route('events') }}",
                    type: "GET",
                    dataType: 'json',
                    success: function(data) {
                        $('#eventsModalTitle').html('Τα διαγωνίσματά μου')
                        $('#eventsModalContent').html(data)
                        $('#eventsModal').addClass("is-active")
                    },
                    error: function(data) {
                        console.log('Error:', data)
                    }
                })
            })

            function triggerDelete(id, title, start){
                $('#eventTitle').html(title)
                $('#eventDate').html('στις ' + start)
                $('#delOk').attr('delId', id)
                $('#delOk').attr('caller', 'eventsModal')
                $('#delModal').addClass('is-active')
                $('#eventsModal').removeClass('is-active')
            }

        </script>

    @endsection
