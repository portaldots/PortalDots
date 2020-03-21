<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="turbolinks-cache-control" content="no-cache">
    <title>
        @hasSection ('title')
            @yield('title') —
        @endif
        {{ config('app.name') }}
    </title>

    @prepend('css')
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css" integrity="sha384-REHJTs1r2ErKBuJB0fCK99gCYsVjwxHrSU0N7I1zl9vZbggVJXRMsv/sLlOAGb4M" crossorigin="anonymous">
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
    @if (config('app.debug'))
        @prepend('js')
            <script defer>
                if (typeof jQuery === 'undefined') {
                    window.jQuery = {
                        noConflict: function () {
                            console.log('do nothing');
                        }
                    };
                }
            </script>
        @endprepend
    @endif
    @stack('js')

    <meta name="format-detection" content="telephone=no">
</head>
<body>

<div class="loading" id="loading">
    <div class="loading-noscript" id="js-noscript">JavaScript を有効にしてください</div>
    <div class="loading-circle"></div>
    <script>
        'use strict';
        {
            const noscript = document.getElementById('js-noscript');
            noscript.parentNode.removeChild(noscript);
        }
    </script>
</div>

<div class="app" id="v2-app">
    <global-events
        v-on:keyup.esc="closeDrawer"
    ></global-events>
    <div
        class="drawer-backdrop"
        v-bind:class="{'is-open': isDrawerOpen}"
        v-on:click="closeDrawer"
    ></div>
    <app-nav-bar>
        @section('navbar')
        <app-nav-bar-toggle
            v-on:click="toggleDrawer"
            ref="toggle"
        ></app-nav-bar-toggle>
        <div class="navbar__title">
            @yield('title', config('app.name'))
        </div>
        @show
    </app-nav-bar>
    <div
        class="drawer"
        v-bind:class="{'is-open': isDrawerOpen}"
        v-on:click="closeDrawer"
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
            <top-alert type="{{ session('topAlert.type', 'primary') }}">
                <template v-slot:title>
                    {{ session('topAlert.title') }}
                </template>

                @if (Session::has('topAlert.body'))
                    {{ session('topAlert.body') }}
                @endif
            </top-alert>
        @endif
        @if ($errors->any())
        <top-alert type="danger">
            <template v-slot:title>
                エラーがあります。以下をご確認ください
            </template>
        </top-alert>
        @endif
        @yield('content')
    </div>
    @section('bottom_tabs')
    @include('v2.includes.bottom_tabs')
    @show
</div>

</body>
</html>
