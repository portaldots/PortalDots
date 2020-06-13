@inject('selectorService', 'App\Services\Circles\SelectorService')

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
        {{ empty(config('app.name')) ? 'PortalDots' : config('app.name') }}
    </title>

    @prepend('css')
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
    @if (config('app.debug'))
        {{-- Laravel Debugbar か Turbolinks かは不明だが、jQuery.noConflict() --}}
        {{-- を呼び出すコードがどこかにあるらしい。jQuery は導入していないため、 --}}
        {{-- jQuery.noConflict() が呼び出されるとエラーになってしまうので、 --}}
        {{-- ダミーの関数を用意する。 --}}
        @prepend('js')
        <script defer>
            if (typeof jQuery === 'undefined') {
                window.jQuery = {
                    noConflict: function() {
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
            'use strict'; {
                const noscript = document.getElementById('js-noscript');
                noscript.parentNode.removeChild(noscript);
            }

        </script>
    </div>

    <div class="app" id="v2-app">
        <global-events v-on:keyup.esc="closeDrawer"></global-events>
        <div class="drawer-backdrop" v-bind:class="{'is-open': isDrawerOpen}" v-on:click="closeDrawer"></div>
        <app-nav-bar @staffpage staff @endstaffpage>
            @section('navbar')
                <app-nav-bar-toggle v-on:click="toggleDrawer" ref="toggle"></app-nav-bar-toggle>
                <div class="navbar__title">
                    @yield('title', config('app.name'))
                </div>
            @show
        </app-nav-bar>
        <div class="drawer" v-bind:class="{'is-open': isDrawerOpen}" v-on:click="closeDrawer" tabindex="0" ref="drawer">
            <div class="drawer__content">
                @section('drawer')
                    @include('v2.includes.drawer')
                @show
            </div>
        </div>
        <div class="content">
            {{-- サークル選択 --}}
            @auth
                @if (count($selectorService->getSelectableCirclesList(Auth::user(), Request::path())) >= 2)
                    <circle-selector-dropdown
                        v-bind:circles="{{ $selectorService->getJsonForCircleSelectorDropdown(Auth::user(), Request::path()) }}"
                        selected-circle-name="{{ $selectorService->getCircle()->name }}"
                        selected-circle-group-name="{{ $selectorService->getCircle()->group_name }}"
                    ></circle-selector-dropdown>
                @endif
            @endauth
            {{-- サークル選択おわり --}}
            @if (Session::has('topAlert.title'))
                <top-alert type="{{ session('topAlert.type', 'primary') }}"
                    {{ (bool) session('topAlert.keepVisible', false) ? 'keep-visible' : '' }}>
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
