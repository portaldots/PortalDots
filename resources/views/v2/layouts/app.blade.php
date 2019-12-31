<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>
        @hasSection ('title')
            @yield('title') —
        @endif
        {{ config('app.name') }}
    </title>

    @prepend('css')
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
        <link href="{{ mix('css/v2/app.css') }}" rel="stylesheet">
    @endprepend
    @stack('css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    @prepend('js')
        <script src="{{ mix('js/v2/app.js') }}" defer></script>
    @endprepend
    @stack('js')

    <meta name="format-detection" content="telephone=no">
</head>
</head>
<body ontouchstart="">

<div class="app" id="v2-app">
    <global-events
        v-on:keyup.esc="closeDrawer"
    ></global-events>
    <div
        class="drawer-backdrop"
        v-bind:class="{'is-open': isDrawerOpen}"
        v-on:click="closeDrawer"
    ></div>
    <div class="navbar">
        @section('navbar')
        <button
            class="navbar-toggle"
            v-on:click="toggleDrawer"
            ref="toggle"
        >
            <img src="{{ url('img/drawerToggle.svg') }}" alt="ドロワーを開閉">
        </button>
        <div class="navbar__title">
            @yield('title', config('app.name'))
        </div>
        @show
    </div>
    <div
        class="drawer"
        v-bind:class="{'is-open': isDrawerOpen}"
        tabindex="0"
        ref="drawer"
    >
        <div class="drawer__content">
        @section('drawer')
        @include('v2.includes.drawer')
        @show
        </div>
    </div>
    <div class="content">
        @yield('content')
    </div>
</div>

</body>
</html>
