@inject('uiThemeService', 'App\Services\Utils\UIThemeService')

@php
$is_iframe = (bool) request()->get('iframe');
@endphp

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title')</title>

    @include('includes.head_ui_theme')

    @prepend('css')
        <link href="{{ mix('css/bootstrap.css') }}" rel="stylesheet">
        <link href="{{ mix('css/fontawesome.css') }}" rel="stylesheet">
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @endprepend
    @stack('css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    @prepend('js')
        <script src="{{ mix('js/manifest.js') }}" defer></script>
        <script src="{{ mix('js/vendor.js') }}" defer></script>
        <script src="{{ mix('js/app.js') }}" defer></script>
    @endprepend
    @stack('js')

    <meta name="format-detection" content="telephone=no">
</head>

<body ontouchstart=""
    class="body-editor-v1 theme-{{ $uiThemeService->getCurrentTheme() }}{{ $is_iframe ? ' is-in-iframe' : '' }}">

    @yield('content')

</body>

</html>
