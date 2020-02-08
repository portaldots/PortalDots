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
    <div class="navbar is-no-drawer">
        @section('navbar')
        {{-- Turbolinks 化が完了したら  data-turbolinks="false" を外す --}}
        <a href="{{ url('/') }}" class="navbar-brand" data-turbolinks="false">
            {{ config('app.name', 'ホームへ戻る') }}
        </a>
        @show
    </div>
    <div class="content is-no-drawer">
        @if (Session::has('topAlert.title'))
            <top-alert type="{{ session('topAlert.type', 'primary') }}" container-medium>
                <template v-slot:title>
                    {{ session('topAlert.title') }}
                </template>

                @if (Session::has('topAlert.body'))
                    {{ session('topAlert.body') }}
                @endif
            </top-alert>
        @endif
        @if ($errors->any())
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
