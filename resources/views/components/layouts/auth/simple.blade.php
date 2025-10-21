<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-light antialiased font-primary">
        <div class='full-center'>
            <div class='login-container'>
                {{ $slot }}
            </div>
        </div>
        @stack('scripts')
        @fluxScripts
    </body>
</html>
