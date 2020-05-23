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
        <div class="loading-circle"></div>
    </div>

    <div class="app" id="v2-app">
        <app-nav-bar no-drawer @staffpage staff @endstaffpage>
            @section('navbar')
                <a href="{{ route('home') }}" class="navbar-brand">
                    {{ config('app.name', 'ホームへ戻る') }}
                </a>
            @show
        </app-nav-bar>
        <div class="content is-no-drawer">
            @if (Session::has('topAlert.title'))
                <top-alert type="{{ session('topAlert.type', 'primary') }}" container-medium
                    {{ (bool) session('topAlert.keepVisible', false) ? 'keep-visible' : '' }}>
                    <template v-slot:title>
                        {{ session('topAlert.title') }}
                    </template>

                    @if (Session::has('topAlert.body'))
                        {{ session('topAlert.body') }}
                    @endif
                </top-alert>
            @endif
            @if (isset($errors) && $errors->any())
                <top-alert type="danger" container-medium>
                    <template v-slot:title>
                        エラーがあります。以下をご確認ください
                    </template>
                </top-alert>
            @endif
            @yield('content')
        </div>
    </div>

</body>

</html>
