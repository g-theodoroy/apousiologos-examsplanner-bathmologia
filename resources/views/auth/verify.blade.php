@extends('layouts.app')

@section('content')
<div class="container">
    <div class="columns is-marginless is-centered">
        <div class="column is-5">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">{{ __('Επιβεβαιώστε το Email σας') }}</p>
                </header>

                <div class="card-content">
                    @if (session('resent'))
                        <div class="notification is-success">
                            <button class="delete"></button>
                            {{ __('Ένας σύνδεσμος επαλήθευσης στάλθηκε στο Email σας.') }}
                        </div>
                    @endif

                    {{ __('Πριν προχωρήσετε, ελέγξτε το email σας για σύνδεσμο επαλήθευσης.') }}
                    {{ __('Αν δεν λάβατε email') }}, <a href="{{ route('verification.resend') }}">{{ __('κάντε κλικ για επαναποστολή') }}</a>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
