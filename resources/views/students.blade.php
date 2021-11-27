@extends('layouts.app')

@section('content')

    <link href="{{ asset('css/dataTables.bulma.min.css') }}" rel="stylesheet">
    <style>
        td.details-control {
            background: url("{{ asset('images/details_open.png') }}") no-repeat center center;
            cursor: pointer;
        }

        tr.shown td.details-control {
            background: url("{{ asset('images/details_close.png') }}") no-repeat center center;
        }

    </style>

    <div class="container">

        <div class="columns is-marginless is-centered">
            <div class="column">
                <nav class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            Μαθητές
                        </p>
                        <a class="button" href="javascript:void(0)" id="newStudent">
                            <span class="icon"><i class="fa fa-user-plus"></i></span>
                            <span>Εγγραφή μαθητή</span>
                        </a>
                    </header>
                    <div class="card-content">
                        <table class="table yajra-datatable is-fullwidth is-striped">
                            <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th class='has-text-centered'>&nbsp;</th>
                                    <th class='has-text-centered'>&nbsp;</th>
                                    <th class='has-text-centered' title="Αρ. Μητρώου">ΑΜ</th>
                                    <th class='has-text-centered' title="Σύνολο Απουσιών">Απ</th>
                                    <th class='has-text-centered'>Επώνυμο</th>
                                    <th class='has-text-centered'>Όνομα</th>
                                    <th class='has-text-centered'>Πατρώνυμο</th>
                                    <th class='has-text-centered'>Τμήματα</th>
                                    <th class='has-text-centered'>Ενέργεια</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </nav>
            </div>
        </div>


        <div id="ajaxModel" class="modal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p id="modalTitle" class="modal-card-title">Εγγραφή Μαθητή</p>
                    <button id="closeModalStudent" class="delete" aria-label="close"></button>
                </header>
                <section class="modal-card-body">
                    <p id='showError' class="help is-danger"></p>

                    <!-- Content ... -->
                    <form id="studentsForm" class="form-horizontal">
                        @csrf

                        <fieldset>

                            <div class="field"><label class="label">Αρ. Μητρώου</label></div>

                            <!-- Text input-->
                            <div class="field is-grouped">
                                <div class="control">
                                    <input id="am" name="am" type="text" placeholder="Αρ. Μητρώου" class="input" required>
                                    <p id='amError' class="help is-danger"></p>
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="field">
                                <label class="label" for="eponimo">Επώνυμο</label>
                                <div class="control">
                                    <input id="eponimo" name="eponimo" type="text" placeholder="Επώνυμο" class="input"
                                        required>
                                    <p id='eponimoError' class="help is-danger"></p>
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="field">
                                <label class="label" for="onoma">Όνομα</label>
                                <div class="control">
                                    <input id="onoma" name="onoma" type="text" placeholder="Όνομα" class="input" required>
                                    <p id='onomaError' class="help is-danger"></p>
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="field">
                                <label class="label" for="patronimo">Πατρώνυμο</label>
                                <div class="control">
                                    <input id="patronimo" name="patronimo" type="text" placeholder="Πατρώνυμο" class="input"
                                        required>
                                    <p id='patronimoError' class="help is-danger"></p>
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="field"><label class="label">Τμήματα</label></div>

                            <div class="field is-grouped">

                                <div class="control">
                                    <input id="t1" name="tmima[]" type="text" placeholder="Τμήμα" class="input">
                                </div>

                                <div class="control">
                                    <input id="t2" name="tmima[]" type="text" placeholder="Τμήμα" class="input">
                                </div>

                            </div>

                            <div class="field is-grouped">

                                <div class="control">
                                    <input id="t3" name="tmima[]" type="text" placeholder="Τμήμα" class="input">
                                </div>

                                <div class="control">
                                    <input id="t4" name="tmima[]" type="text" placeholder="Τμήμα" class="input">
                                </div>

                            </div>

                            <!-- Text input-->
                            <div class="field is-grouped">
                                <div class="control">
                                    <input id="t5" name="tmima[]" type="text" placeholder="Τμήμα" class="input">
                                </div>

                                <div class="control">
                                    <input id="t6" name="tmima[]" type="text" placeholder="Τμήμα" class="input">
                                </div>
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

        <div id="apousiesModel" class="modal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p id="apoumodalTitle" class="modal-card-title">Εισαγωγή απουσιών</p>
                    <button id="apoucloseModalStudent" class="delete" aria-label="close"></button>
                </header>
                <section class="modal-card-body">
                    <p id='apoushowError' class="help is-danger"></p>
                    <div class='container has-text-centered'>
                        <div class='columns is-mobile is-centered'>
                            <!-- Content ... -->
                            <form id="apousiesForm" class="form-horizontal">
                                @csrf


                                <table class="table is-narrow">
                                    <thead>
                                        <tr>
                                            <th class="has-text-centered">Ημερομηνία</th>
                                            @for ($i = 1; $i < $totalHours + 1; $i++)
                                                <th @if ($i % 2 != 0)
                                                    style="background-color: #f2f2f2;"
                                            @endif >{{ $i }}η</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input class="input" id="apousies" name="apousies" value="" type="hidden" />
                                                <input class="input" id="student_id" name="student_id" value=""
                                                    type="hidden" />
                                                <input class="input has-text-centered" id="date" name="date" value=""
                                                    type="text" />
                                                <p id='dateError' class="help is-danger"></p>
                                            </td>
                                            @for ($i = 0; $i < $totalHours; $i++)
                                                <td @if ($i % 2 == 0)
                                                    style="background-color: #f2f2f2;"
                                            @endif >
                                            <input type="checkbox" id="chk{{ $i }}"
                                                onclick="chkClicked(this.checked,{{ $i }})">
                                            </td>
                                            @endfor
                                        </tr>
                                    </tbody>
                                </table>

                            </form>
                        </div>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <button id="apouformSubmit" class="button">Αποθήκευση</button>
                    <button id="apouformReset" class="button">Άκυρο</button>
                </footer>
            </div>
        </div>

    </div>



    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>

    <script src="{{ asset('js/dataTables.bulma.min.js') }}"></script>

    <script type="text/javascript">
        var amForEdit = null
        var subtable = new Array()
        var table = $('.yajra-datatable').DataTable({
            "language": {
                "url": "{{ asset('js/Greek.lang.json') }}"
            },
            processing: true,
            serverSide: true,
            ajax: "{{ route('students.getStudents') }}",
            columns: [{
                    className: 'details-control',
                    orderable: false,
                    data: null,
                    defaultContent: ''
                },
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    className: "has-text-centered"
                },
                {
                    data: 'id',
                    name: 'id',
                    className: "has-text-centered"
                },
                {
                    data: 'sumap',
                    name: 'sumap',
                    className: "has-text-centered"
                },
                {
                    data: 'eponimo',
                    name: 'eponimo'
                },
                {
                    data: 'onoma',
                    name: 'onoma'
                },
                {
                    data: 'patronimo',
                    name: 'patronimo'
                },
                {
                    data: 'tmimata',
                    name: 'tmimata'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    className: "has-text-centered"
                },
            ],
            order: [
                [4, 'asc']
            ],
        });

        $('body').on('click', '.edit', function() {
            var am = this.id
            $.get("{{ route('students.edit') }}" + "/" + am, function(data) {
                amForEdit = am
                $('#ajaxModel').addClass("is-active");
                $('#modalTitle').html("Επεξεργασία μαθητή");
                $('#am').val(data.id);
                $('#eponimo').val(data.eponimo);
                $('#onoma').val(data.onoma);
                $('#patronimo').val(data.patronimo);
                $('#t1').val(data.t1);
                $('#t2').val(data.t2);
                $('#t3').val(data.t3);
                $('#t4').val(data.t4);
                $('#t5').val(data.t5);
                $('#t6').val(data.t6);
            })
        })

        $('body').on('click', '.del', function() {
            var am = this.id
            if (!confirm("Θέλετε σίγουρα να διαγράψετε τον μαθητή;")) return  false
            $.ajax({
                type: "DELETE",
                url: "{{ route('students.delete') }}" + '/' + am,
                dataType: 'JSON',
                data: {
                    '_token': '{{ csrf_token() }}',
                },
                success: function(data) {
                    table.ajax.reload()
                },
                error: function(data) {
                    console.log('Error:', data);
                }
            });
        })

        $('body').on('click', '#newStudent', function() {
            $('#ajaxModel').addClass("is-active");
        })
        $('body').on('click', '#closeModalStudent', function() {
            $('#studentsForm').trigger("reset")
            $('.help').html("")
            $('#ajaxModel').removeClass("is-active");
            $('#modalTitle').html("Εγγραφή μαθητή");
            amForEdit = null
        })

        $('body').on('click', '#formSubmit', function() {

            if (!$.trim($('#am').val())) {
                $('#amError').html("Συμπληρώστε τον Αρ. Μητρώου")
                $('#am').focus()
                $('#am').val('')
                return false
            } else {
                var amForCheck = $.trim($('#am').val())
                if( !(parseInt( amForCheck ) == amForCheck && amForCheck > 0)){
                    $('#amError').html("O Αρ. Μητρώου πρέπει να είναι θετικός αριθμός")
                    $('#am').focus()
                    $('#am').val(amForCheck)
                    return false
                }else{
                    $('#amError').html("")
                }
            }
            if (!$.trim($('#eponimo').val())) {
                $('#eponimoError').html("Συμπληρώστε το Επώνυμο")
                $('#eponimo').focus()
                $('#eponimo').val('')
                return false
            } else {
                $('#eponimoError').html("")
            }
            if (!$.trim($('#onoma').val())) {
                $('#onomaError').html("Συμπληρώστε το Όνομα")
                $('#onoma').focus()
                $('#onoma').val('')
                return false
            } else {
                $('#onomaError').html("")
            }
            if (!$.trim($('#patronimo').val())) {
                $('#patronimoError').html("Συμπληρώστε το Πατρώνυμο")
                $('#patronimo').focus()
                $('#patronimo').val('')
                return false
            } else {
                $('#patronimoError').html("")
            }

            $.get("{{ route('students.unique') }}" + "/" + amForCheck, function(data) {
                if(data == 1 && amForCheck !== amForEdit){
                    $('#amError').html("O Αρ. Μητρώου είναι ήδη καταχωρισμένος")
                    $('#am').focus()
                    $('#am').val(amForCheck)
                    return false
                }
                $.ajax({
                    data: $('#studentsForm').serialize(),
                    url: "{{ route('students.store') }}",
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        $('#studentsForm').trigger("reset")
                        $('.help').html("")
                        $('#ajaxModel').removeClass("is-active")
                        amForEdit = null
                        table.ajax.reload()
                    },
                    error: function(data) {
                        console.log('Error:', data)
                        $('#showError').html(data.responseJSON.message)
                    }
                })
            })
        })

        $('body').on('click', '#formReset', function() {
            $('#studentsForm').trigger("reset")
            $('.help').html("")
            $('#ajaxModel').removeClass("is-active")
            amForEdit = null
        })

        $('.yajra-datatable tbody').on('click', 'td.details-control', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr)
            var am = row.data().id

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                delete subtable[am]
            } else {

                var name = row.data().eponimo + ' ' + row.data().onoma
                row.child(`<table id='details` + am + `' class ="table is-bordered is-striped" style=" margin-left:auto;margin-right: auto;">
                               <thead>
                               <tr style="background-color: #f2f2f2;" >
                               <th rowspan="2">Ημ/νια</th>
                               <th colspan ="2">Σύνολα</th>
                               <th colspan ="7">Ώρες</th>
                               <th>Ενέργεια</th>
                               </tr>
                               <tr style="background-color: #f2f2f2;" >
                               <th>Όλες</th>
                               <th>Ημέρας</th>
                               <th>1η</th>
                               <th>2η</th>
                               <th>3η</th>
                               <th>4η</th>
                               <th>5η</th>
                               <th>6η</th>
                               <th>7η</th>
                               <th>
                               <a class="button" href="javascript:void(0)" id="newApousia" name="` + am + '@&#' + name + `" >
                               <span class="icon">
                                 <i class="fa fa-user-plus"></i>
                                 </span>
                                 </a>
                                </th>
                               </tr>
                               </thead>
                               </table>`).show();
                tr.addClass('shown');
                //     })
                subtable[am] = $('#details' + am).DataTable({
                    "language": {
                        "url": "{{ asset('js/Greek.lang.json') }}"
                    },
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('students.apousies') }}" + "/" + am,
                    columnDefs: [{
                        className: "has-text-centered",
                        targets: '_all'
                    }],
                    columns: [{
                            data: 'date',
                            name: 'date'
                        },
                        {
                            data: 'total',
                            name: 'total'
                        },
                        {
                            data: 'sumOfDay',
                            name: 'sumOfDay'
                        },
                        {
                            data: 'ora1',
                            name: 'ora1'
                        },
                        {
                            data: 'ora2',
                            name: 'ora2'
                        },
                        {
                            data: 'ora3',
                            name: 'ora3'
                        },
                        {
                            data: 'ora4',
                            name: 'ora4'
                        },
                        {
                            data: 'ora5',
                            name: 'ora5'
                        },
                        {
                            data: 'ora6',
                            name: 'ora6'
                        },
                        {
                            data: 'ora7',
                            name: 'ora7'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false
                        },
                    ],
                    order: [
                        [1, 'desc']
                    ],
                });

            }
        });

        $('body').on('click', '#newApousia', function() {
            $('#apousiesForm').trigger("reset")
            var data = this.name.split('@&#');
            $('#apousiesModel').addClass("is-active");
            $('#apoumodalTitle').html("Εισαγωγή απουσιών<br><br>" + data[1])
            $('#student_id').val(data[0])
            $('#date').prop('readonly', false)
            $('.help').html("")
        })

        $('body').on('click', '.apouedit', function() {
            var id = this.id
            $.get("{{ route('apousies.edit') }}" + "/" + id, function(data) {
                $('#apousiesModel').addClass("is-active");
                $('#apoumodalTitle').html("Επεξεργασια απουσιών<br><br>" + data.name);
                $('#student_id').val(data.student_id)
                $('#apousies').val(data.apousies)
                $('#date').val(data.date)
                $('#date').prop('readonly', true)
                for (x = 0; x < {{ $totalHours }}; x++) {
                    if (data.apousies.substr(x, 1) == '1') {
                        $('#chk' + x).prop('checked', true)
                    } else {
                        $('#chk' + x).prop('checked', false)
                    }
                }
                $('.help').html("")
            })
        })

        $('body').on('click', '.apoudel', function() {
            var id = this.id
            if (!confirm("Θέλετε σίγουρα να διαγράψετε τις απουσίες;")) return false
            $.ajax({
                type: "DELETE",
                url: "{{ route('apousies.delete') }}" + '/' + id,
                dataType: 'JSON',
                data: {
                    '_token': '{{ csrf_token() }}',
                },
                success: function(data) {
                    subtable.forEach(function(sbt) {
                        sbt.ajax.reload()
                    })
                },
                error: function(data) {
                    console.log('Error:', data);
                }
            });
        })
        $('body').on('click', '#apoucloseModalStudent', function() {
            $('#apousiesForm').trigger("reset")
            $('#apousiesModel').removeClass("is-active");
            $('.help').html("")
        })

        $('body').on('click', '#apouformReset', function() {
            $('#apousiesForm').trigger("reset")
            $('#apousiesModel').removeClass("is-active")
            $('.help').html("")
        })

        $('body').on('click', '#apouformSubmit', function() {

            if (!$.trim($('#date').val())) {
                $('#dateError').html("Συμπληρώστε την Ημερομηνία")
                $('#date').focus()
                $('#date').val('')
                return false
            } else {
                $('#dateError').html("")
            }
            if (!isDDMMYY($('#date').val())) {
                $('#dateError').html("Χρησιμοποιείστε τη μορφή ηη/μμ/εε")
                $('#date').focus()
                return false
            } else {
                $('#dateError').html("")
            }

            $.ajax({
                data: $('#apousiesForm').serialize(),
                url: "{{ route('apousies.store') }}",
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    $('.help').html("")
                    $('#apousies').val("")
                    $('#apousiesModel').removeClass("is-active")
                    subtable.forEach(function(sbt) {
                        sbt.ajax.reload()
                    })
                },
                error: function(data) {
                    console.log('Error:', data)
                    $('#apoushowError').html(data.responseJSON.message)
                }
            })
        })


        String.prototype.replaceAt = function(index, replacement) {
            return this.substr(0, index) + replacement + this.substr(index + replacement.length);
        }

        function chkClicked(checked, position) {
            var apval = document.getElementById('apousies').value
            if (checked == true) {
                if (!apval) apval = "{{ str_repeat('0', $totalHours) }}"
                apval = apval.replaceAt(position, "1")
            } else {
                apval = apval.replaceAt(position, "0")
                if (apval == "{{ str_repeat('0', $totalHours) }}") apval = ''
            }
            document.getElementById('apousies').value = apval
        }

        function isDDMMYY(str) {
            var regex = /^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{2}$/
            return regex.test(str)
        }

    </script>

@endsection
