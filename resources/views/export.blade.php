@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="columns is-marginless is-centered">
            <div class="column is-10">
                <nav class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            Εξαγωγή απουσιών για τις Ημερομηνίες
                        </p>
                        <p class="card-header-title">
                            κενές ημ/νιες = σήμερα: {{ Carbon\Carbon::Now()->format('d/m/y') }}
                        </p>
                    </header>
                    <form name="formexport" id="formexport" role="form" method="POST"
                        action="{{ url('/export/apouxls') }}" onsubmit="return chkDateFormat()" >
                        {{ csrf_field() }}
                        <div class="card-content">
                            <nav class="level">

                                <div class="level-item">
                                    <div class="field-label is-normal">
                                        <label class="label">Από&nbsp;</label>
                                    </div>
                                    <div class="field-body">
                                        <div>
                                            <p class="control">
                                                <input name="apoDate" id="apoDate" class="input" type="text" placeholder="ηη/μμ/εε"
                                                    size="5">
                                            </p>
                                        </div>
                                    </div>
                                </div>


                                <div class="level-item">
                                    <div class="field-label is-normal">
                                        <label class="label">Έως&nbsp;</label>
                                    </div>
                                    <div class="field-body">
                                        <div>
                                            <p class="control">
                                                <input name="eosDate" id="eosDate" class="input" type="text" placeholder="ηη/μμ/εε"
                                                    size="5">
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="level-item">
                                    <button class="field button" type="submit">
                                        <span class="icon">
                                            <i class="fa fa-download"></i>
                                        </span>
                                        <span>Εξαγωγή xls</span>
                                    </button>
                                </div>

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
                            Εξαγωγή αρχείου 187.xls όλων των μαθητών όλων των τάξεων για καταχώριση στο myschool
                        </p>
                    </header>

                    <div class="card-content">
                        <nav class="level">

                            @if (\App\Config::getConfigValueOf('activeGradePeriod'))

                                <div class="level-item">
                                    <p class="card-header-title">
                                        Βαθμολογική περίοδος:
                                    </p>
                                </div>

                                <div class="level-item">
                                    <p class="card-header-title">
                                        {{ \App\Period::find(\App\Config::getConfigValueOf('activeGradePeriod'))->period }}
                                    </p>
                                </div>

                                <div class="level-item">
                                    <a class="field button" href="{{ route('gradesxls') }}">
                                        <span class="icon">
                                            <i class="fa fa-download"></i>
                                        </span>
                                        <span>Εξαγωγή xls</span>
                                    </a>
                                </div>

                            @else

                                <div class="level-item">
                                    <p class="card-header-title">
                                        Επιλέξτε Βαθμολογική περίοδο στις &nbsp;<a href="{{ route('admin') }}">
                                            ρυθμίσεις</a>
                                    </p>
                                </div>

                            @endif

                        </nav>

                        @if (\App\Config::getConfigValueOf('activeGradePeriod'))
                            <p class="has-text-weight-bold is-size-6">Οδηγίες:</p>
                            <div class="level-item">
                                <ol>
                                    <li>Αντιγράψτε το περιεχόμενο του αρχείου xls και επικολλείστε το σε ένα αρχείο
                                        <strong>187.xls</strong> που εξάγατε από το myschool.</li>
                                    <li>Φροντίστε κατά την επικόλληση το κελί με την επικεφαλίδα <strong>"Α/Α"</strong> να
                                        συμπέσει με το αρχικό.</li>
                                    <li>Χρησιμοποιείστε το αρχείο <strong>187.xls</strong> και κάνετε τρεις εισαγωγές
                                        βαθμολογίας μία για κάθε τάξη.</li>
                                </ol>
                            </div>
                        @endif

                </nav>
            </div>
        </div>

        <div class="columns is-marginless is-centered">
            <div class="column is-10">
                <nav class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            Ενημέρωση των εξηχθέντων αρχείων 187.xls για κάθε τάξη από το myschool
                        </p>
                    </header>

                    <form name="frm" id="frm" role="form" method="POST" action="{{ url('export/populateXls') }}"
                        enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="card-content">
                            <nav class="level">

                                @if (\App\Config::getConfigValueOf('activeGradePeriod'))

                                    <div class="level-item">
                                        <p class="card-header-title">
                                            {{ \App\Period::find(\App\Config::getConfigValueOf('activeGradePeriod'))->period }}
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
                                                <i class="fa fa-download"></i>
                                            </span>
                                            <span>Ενημέρωση xls</span>
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
                                                <input name="labelsRow" class="input" type="text" size="1"
                                                    value="{{ App\Config::getconfigValueOf('187XlsLabelsRow') }}">
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
                                                <input name="amCol" class="input" type="text" size="1"
                                                    value="{{ App\Config::getconfigValueOf('187XlsAmCol') }}">
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
                                                <input name="firstLessonCol" class="input" type="text" size="1"
                                                    value="{{ App\Config::getconfigValueOf('187XlsFirstLessonCol') }}">
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            @else

                                <div class="level-item">
                                    <p class="card-header-title">
                                        Επιλέξτε Βαθμολογική περίοδο στις &nbsp;<a href="{{ route('admin') }}">
                                            ρυθμίσεις</a>
                                    </p>
                                </div>

                                @endif


                            </nav>

                        </div>
                    </form>
                </nav>

            </div>
        </div>


    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        const fileInput = document.querySelector('#file-xls input[type=file]');
        fileInput.onchange = () => {
            if (fileInput.files.length > 0) {
                document.getElementById("sbmt_xls").disabled = false;
                const fileName = document.querySelector('#file-xls .file-name');
                fileName.textContent = fileInput.files[0].name;
            }
        }

        function chkDateFormat() {
            apoDate = $("#apoDate").val()
            eosDate = $("#eosDate").val()
            if (apoDate && !apoDate.match(/^(\d{2})\/(\d{2})\/(\d{2})$/)) {
                alert('Η ημερομηνία πρέπει να έχει τη μορφή "ηη/μμ/εε"')
                $("#apoDate").focus();
                return false
            }
            if (eosDate && !eosDate.match(/^(\d{2})\/(\d{2})\/(\d{2})$/)) {
                alert('Η ημερομηνία πρέπει να έχει τη μορφή "ηη/μμ/εε"')
                $("#eosDate").focus();
                return false
            }
            return true
        }

    </script>
@endsection
