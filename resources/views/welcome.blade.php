<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-image: url("{{url('/images/background.png')}}");
                background-size: cover;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 10px;
            }

            .content {
                text-align: center;
            }

            .title {
                color: DarkSlateBlue;
                text-shadow: 3px 3px #FFFFF0;
                font-size: 25px;
                font-weight: 1800;
            }

            .links > a {
                color: DarkSlateBlue;
                text-shadow: 2px 2px #FFFFF0;
                padding: 0 10px;
                font-size: 18px;
                font-weight: 600;
                letter-spacing: .03rem;
                text-decoration: none;
            }

            .versioninfo {
                color: grey;
                text-shadow: 2px 2px #FFFFF0;
                padding: 0 25px;
                font-size: 30px;
                font-weight: 1200;
                letter-spacing: .1rem;
                text-decoration: none;
            }

            .m-b-md {
              margin-top: 50px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @if (Auth::check())
                        <a href="{{ url('/home/0') }}">Αρχική</a>
                    @else
                        <a href="{{ url('/login') }}">Είσοδος</a>
                        @if($allowRegister)
                        <a href="{{ url('/register') }}">Εγγραφή</a>
                        @endif
                    @endif
                </div>
            @endif

            <div class="content">
              <div class="title m-b-md">
                <a href="{{ url('/about') }}">
                  <img src="{{url('/images/logo.png')}}" />
                </a>
                  <p>Ηλεκτρονικός Απουσιολόγος &
                  <br>Προγραμματιστής Διαγωνισμάτων &
                  <br>Καταχώριση Βαθμολογίας
                  <br>{{ $schoolName }}</p>
                  <p class="versioninfo">GΘ @ Laravel</p>
                </div>
            </div>
        </div>
    </body>
</html>
