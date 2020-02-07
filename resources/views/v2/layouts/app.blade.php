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
        <link href="{{ mix('css/v2/app.css') }}" rel="stylesheet">
    @endprepend
    @stack('css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    @prepend('js')
        <script src="{{ mix('js/v2/app.js') }}" defer></script>
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
    <div class="loading-circle"></div>
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
        @auth
            @unless (Auth::user()->areBothEmailsVerified())

                <top-alert type="primary">
                    <template v-slot:title>
                        <i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i>
                        メール認証を行ってください
                    </template>

                    {{ config('app.name') }}の全機能を利用するには、次のメールアドレス宛に送信された確認メール内のURLにアクセスしてください。
                    <strong>
                    @unless (Auth::user()->hasVerifiedUnivemail())
                        {{ Auth::user()->univemail }}
                        @unless (Auth::user()->hasVerifiedEmail())
                            •
                        @endunless
                    @endunless
                    @unless (Auth::user()->hasVerifiedEmail())
                        {{ Auth::user()->email }}
                    @endunless
                    </strong>

                    <template v-slot:cta>
                        <form action="{{ route('verification.resend') }}" method="post">
                            @csrf
                            <button class="btn is-primary-inverse is-no-border is-wide">
                                <strong>確認メールを再送</strong>
                            </button>
                        </form>
                    </template>
                </top-alert>
            @endunless
        @endauth
        @if (Session::has('topAlert.title'))
            <top-alert type="primary">
                <template v-slot:title>
                    {{ session('topAlert.title') }}
                </template>

                @if (Session::has('topAlert.body'))
                    {{ session('topAlert.body') }}
                @endif
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
