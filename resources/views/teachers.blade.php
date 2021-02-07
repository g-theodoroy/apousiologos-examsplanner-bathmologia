@extends('layouts.app')

@section('content')

    <link href="{{ asset('css/dataTables.bulma.min.css') }}" rel="stylesheet">

    <div class="container">

        <div class="columns is-marginless is-centered">
            <div class="column">
                <nav class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            Καθηγητές
                        </p>
                        <a class="button" href="javascript:void(0)" id="newTeacher">
                            <span class="icon"><i class="fa fa-user-plus"></i></span>
                            <span>Εγγραφή καθηγητή</span>
                        </a>
                    </header>
                    <div class="card-content">
                        <table class="table yajra-datatable is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th class='has-text-centered' style="background-color: #f2f2f2;">Α/Α</th>
                                    <th class='has-text-centered' style="background-color: #f2f2f2;">Ονοματεπώνυμο</th>
                                    <th class='has-text-centered' style="background-color: #f2f2f2;">E-mail</th>
                                    <th class='has-text-centered' style="background-color: #f2f2f2;">Τμήμα -> Μάθημα</th>
                                    <th class='has-text-centered' style="background-color: #f2f2f2;">Ενέργεια</th>
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
                    <p id="modalTitle" class="modal-card-title">Εγγραφή Καθηγητή</p>
                    <button id="closeModalTeacher" class="delete" aria-label="close"></button>
                </header>
                <section class="modal-card-body">

                    <!-- Content ... -->
                    <form id="teachersForm" class="form-horizontal">
                        @csrf

                        <input id="id" name="id" type="hidden" class="input">

                        <fieldset>
                            <div class="field">
                                <p id='showError' class="help is-danger"></p>
                            </div>

                            <!-- Text input-->
                            <div class="field">
                                <label class="label" for="name">Ονοματεπώνυμο</label>
                                <div class="control">
                                    <input id="name" name="name" type="text" placeholder="Ονοματεπώνυμο" class="input"
                                        required>
                                    <p id='nameError' class="help is-danger"></p>
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="field">
                                <label class="label" for="email">Email</label>
                                <div class="control">
                                    <input id="email" name="email" type="text" placeholder="Email" class="input" required>
                                    <p id='emailError' class="help is-danger"></p>
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="field">
                                <label class="label" for="password">Password (μόνο για αλλαγή)</label>
                                <div class="control">
                                    <input id="password" name="password" type="text" placeholder="Password" class="input">
                                    <p id='passwordError' class="help is-danger"></p>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label" for="tmimata">Τμήμα -> μάθημα (ένα σε κάθε γραμμή διαχωρισμένα με "->")</label>
                                <div class="control">
                                    <textarea id="tmimata" name="tmimata" class="textarea"></textarea>
                                    <p id='tmimataError' class="help is-danger"></p>
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

    </div>



    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bulma.min.js') }}"></script>

    <script type="text/javascript">
        var table = $('.yajra-datatable').DataTable({
            "language": {
                "url": "{{ asset('js/Greek.lang.json') }}"
            },
            processing: true,
            serverSide: true,
            ajax: "{{ route('teachers.getTeachers') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'tmimata',
                    name: 'tmimata',
                    render: function ( data, type, row ) {
                       return row.tmimata.join('<br>');
                    }
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ]
        })
        $('body').on('click', '.edit', function() {

            var id = this.id
            $.get("{{ route('teachers.edit') }}" + "/" + id, function(data) {
                $('#ajaxModel').addClass("is-active");
                $('#modalTitle').html("Επεξεργασία καθηγητή");
                $('#id').val(data.id);
                $('#name').val(data.name);
                $('#email').val(data.email);
                $('#tmimata').val(data.tmimata);
                $('#mathimata').val(data.mathimata);
            })
        })

        $('body').on('click', '.del', function() {

            var id = this.id
            if (!confirm("Θέλετε σίγουρα να διαγράψετε τον καθηγητή;")) return
            $.ajax({
                type: "DELETE",
                url: "{{ route('teachers.delete') }}" + '/' + id,
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

        $('body').on('click', '#newTeacher', function() {
            $('#ajaxModel').addClass("is-active");
            $('#modalTitle').html("Εγγραφή καθηγητή");
        })
        $('body').on('click', '#closeModalTeacher', function() {
            $('#teachersForm').trigger("reset")
            $('.help').html("")
            $('#ajaxModel').removeClass("is-active");
        })

        $('body').on('click', '#formSubmit', function() {

            if (!$.trim($('#name').val())) {
                $('#nameError').html("Συμπληρώστε το Ονοματεπώνυμο")
                $('#name').focus()
                $('#name').val('')
                return
            } else {
                $('#nameError').html("")
            }
            if (!$.trim($('#email').val())) {
                $('#emailError').html("Συμπληρώστε το Email")
                $('#email').focus()
                $('#email').val('')
                return
            } else {
                $('#emailError').html("")
            }
            if (!$.trim($('#id').val()) && !$.trim($('#password').val())) {
                $('#passwordError').html("Συμπληρώστε το Password")
                $('#password').focus()
                $('#password').val('')
                return
            } else {
                $('#emailError').html("")
            }

            $.ajax({
                data: $('#teachersForm').serialize(),
                url: "{{ route('teachers.store') }}",
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    $('#teachersForm').trigger("reset")
                    $('.help').html("")
                    $('#ajaxModel').removeClass("is-active")
                    table.ajax.reload()
                },
                error: function(data) {
                    console.log('Error:', data)
                    $('#showError').html(data.responseJSON.message)
                }
            })
        })

        $('body').on('click', '#formReset', function() {
            $('#teachersForm').trigger("reset")
            $('.help').html("")
            $('#ajaxModel').removeClass("is-active")
        })

    </script>

@endsection
