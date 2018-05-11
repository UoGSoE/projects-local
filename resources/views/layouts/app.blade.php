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
        <div class="container">

            <div>
                <noscript>
                    This website requires javascript to function. Please turn it on.
                </noscript>

                @include('layouts.errors')
                @if (session('success'))
                    <article class="message is-success">
                        <div class="message-body">
                            {{ session('success') }}
                        </div>
                    </article>
                @endif

                @yield('content')
            </div>

            <div>
                @include('layouts.footer')
            </div>

        </div>
    </section>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
