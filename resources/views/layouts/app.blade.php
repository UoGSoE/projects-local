<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script>
        window.config = {
            'required_choices': @json(config('projects.required_choices'))
        };
        window.user = @json(Auth::user());
    </script>

    @routes
</head>
<body>
    @include('layouts.navbar')
    <section id="app" class="section">
        <div class="container" id="content-area">
            <div v-cloak id="content-main">
                <noscript>
                    This website requires javascript to function. Please turn it on.
                </noscript>

                @include('layouts.errors')
                @if (session('success'))
                    <article class="message is-success" id="success-box">
                        <div class="message-body">
                            {{ session('success') }}
                        </div>
                    </article>
                @endif

                @yield('content')
            </div>
        </div>
    </section>
    @include('layouts.footer')

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        (function() {
            setTimeout(() => {
                const box = document.querySelector('#success-box');
                if (box) {
                    box.classList.add('fadeout-success');
                }
            }, 2000);
        })();
    </script>
    @stack('scripts')
</body>
</html>
