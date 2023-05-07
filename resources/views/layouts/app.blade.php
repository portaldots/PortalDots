@inject('uiThemeService', 'App\Services\Utils\UIThemeService')

@php
    $is_iframe = (bool) request()->get('iframe');
@endphp

<!DOCTYPE html>
<html lang="ja" class="theme-{{ $uiThemeService->getCurrentTheme() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="turbolinks-cache-control" content="no-cache">
    @stack('meta')

    <title>
        @hasSection('title')
            @yield('title') —
        @endif
        {{ empty(config('app.name')) ? 'PortalDots' : config('app.name') }}
    </title>

    @include('includes.head_ui_theme')

    @prepend('css')
        @vite(['resources/sass/app.scss'])
    @endprepend
    @stack('css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    @prepend('js')
        @vite(['resources/js/v2/app.js'])
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

<body class="{{ $is_iframe ? 'is-in-iframe' : '' }} @stack('body-class')">
    @include('includes.loading')
    <div class="app" id="v2-app">
        <global-events v-on:keyup.esc="closeDrawer"></global-events>
        <div class="drawer-backdrop" v-bind:class="{'is-open': isDrawerOpen}" v-on:click="closeDrawer"></div>
        @if (!$is_iframe)
            <app-nav-bar @staffpage staff @endstaffpage>
                @section('navbar')
                    <app-nav-bar-toggle v-on:click="toggleDrawer" ref="toggle"></app-nav-bar-toggle>
                    <div class="navbar__title">
                        @yield('title', config('app.name'))
                    </div>
                @show
            </app-nav-bar>
            <div class="drawer" v-bind:class="{'is-open': isDrawerOpen}" v-on:click="closeDrawer" tabindex="0"
                ref="drawer">
                <div class="drawer__content">
                    @section('drawer')
                        @include('includes.drawer')
                    @show
                </div>
            </div>
        @endif
        <div class="content{{ $is_iframe ? ' is-no-navbar is-no-drawer' : '' }}">
            <div class="content__body">
                @include('includes.top_circle_selector')
                @if (Session::has('topAlert.title'))
                    <top-alert type="{{ session('topAlert.type', 'primary') }}"
                        {{ (bool) session('topAlert.keepVisible', false) ? 'keep-visible' : '' }} @yield('top_alert_props', '')>
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
            @if (
                !$is_iframe &&
                    empty(trim($__env->yieldContent('no_footer')) /* ← no_footer という section がセットされていない場合 true */))
                <app-footer>{{ config('app.name') }}</app-footer>
            @endif
        </div>
        @if (!Request::is('staff*') && !Request::is('admin*'))
            @section('bottom_tabs')
                @include('includes.bottom_tabs')
            @show
        @endif
    </div>

</body>

</html>
