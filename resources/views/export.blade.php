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
                    <form name="formexport" id="formexport" role="form" method="POST" action="{{ url('/export/apouxls') }}">
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
                                                <input name="apoDate" class="input" type="text" placeholder="ηη/μμ/εε"
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
                                                <input name="eosDate" class="input" type="text" placeholder="ηη/μμ/εε"
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
                            Εξαγωγή 187.xls για καταχώριση στο myschool 
                        </p>
                    </header>

                    <div class="card-content">
                            <nav class="level">

                                @if( \App\Config::getConfigValueOf('activeGradePeriod'))

                                    <div class="level-item">
                                        <p class="card-header-title">
                                        Βαθμολογική περίοδος:
                                        </p>
                                    </div>

                                    <div class="level-item">
                                        <p class="card-header-title">
                                        {{\App\Period::find(\App\Config::getConfigValueOf('activeGradePeriod'))->period}}
                                        </p>
                                    </div>

                                    <div class="level-item">
                                        <a class="field button" href="{{route('gradesxls')}}">
                                            <span class="icon">
                                                <i class="fa fa-download"></i>
                                            </span>
                                            <span>Εξαγωγή xls</span>
                                        </a>
                                    </div>

                                @else

                                    <div class="level-item">
                                        <p class="card-header-title">
                                        Επιλέξτε Βαθμολογική περίοδο στις &nbsp;<a href="{{ route('admin')}}"> ρυθμίσεις</a>
                                        </p>
                                    </div>

                                 @endif

                            </nav>

                            @if( \App\Config::getConfigValueOf('activeGradePeriod'))
                                <p class="has-text-weight-bold is-size-6">Οδηγίες:</p> 
                                <div class="level-item">
                                    <ol>
                                        <li>Αντιγράψτε το περιεχόμενο του αρχείου xls και επικολλείστε το σε ένα αρχείο <strong>187.xls</strong> που εξάγατε από το myschool.</li>
                                        <li>Φροντίστε κατά την επικόλληση το κελί με την επικεφαλίδα <strong>"Α/Α"</strong> να συμπέσει με το αρχικό.</li>
                                        <li>Χρησιμοποιείστε το αρχείο <strong>187.xls</strong> και κάνετε τρεις εισαγωγές βαθμολογίας μία για κάθε τάξη.</li>
                                    </ol>
                                </div>
                            @endif

                </nav>
            </div>
        </div>


    </div>

    <script>

    </script>
@endsection
