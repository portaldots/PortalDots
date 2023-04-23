@inject('uiThemeService', 'App\Services\Utils\UIThemeService')

<!DOCTYPE html>
<html lang="ja" class="theme-{{ $uiThemeService->getCurrentTheme() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title')</title>

    @include('includes.head_ui_theme')

    @prepend('css')
        @vite(['resources/sass/bootstrap.scss', 'resources/sass/app.scss'])
    @endprepend
    @stack('css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    @prepend('js')
        @vite(['resources/js/app.js'])
    @endprepend
    @stack('js')

    <meta name="format-detection" content="telephone=no">
</head>

<body ontouchstart="" class="body-editor-v1">

    @yield('content')

</body>

</html>
