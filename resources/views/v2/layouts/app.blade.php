<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        @if (Session::has('topAlert.title'))
            <div class="top_alert is-primary">
                <h2 class="top_alert__title">{{ session('topAlert.title') }}</h2>
                @if (Session::has('topAlert.body'))
                    <p class="top_alert__body">{{ session('topAlert.body') }}</p>
                @endif
            </div>
        @endif
        @yield('content')
    </div>
    @section('bottom_tabs')
    <div class="bottom_tabs">
        <div class="container is-medium bottom_tabs-container">
            {{-- TODO: Request::is の引数は将来的に '' (空文字) にしたい --}}
            <a href="{{ url('/') }}" class="bottom_tabs-tab{{ Request::is('login') || Request::is('home*') ? ' is-active' : '' }}">
                <i class="fas fa-home bottom_tabs-tab__icon"></i>
                <div class="bottom_tabs-tab__label">ホーム</div>
            </a>
            <a href="{{ route('pages.index') }}" class="bottom_tabs-tab{{ Request::is('pages*') ? ' is-active' : '' }}">
                <i class="fas fa-bullhorn bottom_tabs-tab__icon"></i>
                <div class="bottom_tabs-tab__label">お知らせ</div>
            </a>
            <a href="{{ route('documents.index') }}" class="bottom_tabs-tab{{ Request::is('documents*') ? ' is-active' : '' }}">
                <i class="far fa-file-alt bottom_tabs-tab__icon"></i>
                <div class="bottom_tabs-tab__label">配布資料</div>
            </a>
            <a href="{{ url('home/applications') }}" class="bottom_tabs-tab">
                <i class="far fa-edit bottom_tabs-tab__icon"></i>
                <div class="bottom_tabs-tab__label">申請</div>
            </a>
            <a href="{{ route('contacts') }}" class="bottom_tabs-tab{{ Request::is('contacts*') ? ' is-active' : '' }}">
                <i class="far fa-envelope bottom_tabs-tab__icon"></i>
                <div class="bottom_tabs-tab__label">お問い合わせ</div>
            </a>
        </div>
    </div>
    @show
</div>

</body>
</html>
