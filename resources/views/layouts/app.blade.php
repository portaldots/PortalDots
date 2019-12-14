@section('global_nav')
    @staffpage
        <li class="nav-item">
            <a class="nav-link" href="{{ url('/home') }}">
                <i class="fas fa-fw fa-home nav-icon"></i>
                <span class="nav-label">ホーム</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('/home/pages') }}">
                <i class="fas fa-fw fa-newspaper nav-icon"></i>
                <span class="nav-label">お知らせ</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('/home/applications') }}">
                <i class="far fa-fw fa-edit nav-icon"></i>
                <span class="nav-label">申請</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('/home/documents') }}">
                <i class="far fa-fw fa-file-alt nav-icon"></i>
                <span class="nav-label">配布資料</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('/home/schedules') }}">
                <i class="far fa-fw fa-calendar-alt nav-icon"></i>
                <span class="nav-label">予定</span>
            </a>
        </li>
    @endstaffpage
@endsection

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    @prepend('css')
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @endprepend
    @stack('css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    @prepend('js')
        <script src="{{ mix('js/app.js') }}" defer></script>
    @endprepend
    @stack('js')

    <meta name="format-detection" content="telephone=no">
</head>
</head>
<body ontouchstart="">

<nav class="navbar fixed-top navbar-expand navbar-light app-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
            @if(config('env') !== 'production')
                <span class="badge badge-dark badge-pill">dev</span>
            @endif
        </a>

        @auth
            @if (Gate::forUser(Auth::user())->allows('use-all-features'))
                <ul class="navbar-nav app-navbar-nav d-none d-lg-flex">
                    @yield('global_nav')
                </ul>
            @endif
        @endauth
        <ul class="navbar-nav ml-auto">
            @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline">
                            {{ Auth::user()->name }}
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            @if (!empty($student_id = Auth::user()->student_id))
                                <h6 class="dropdown-header">
                                    {{ mb_strtoupper($student_id) }}
                                </h6>
                            @endif
                        <a class="dropdown-item" href="{{ route('change_password') }}">パスワードの変更</a>
                        <a class="dropdown-item" href="{{ route('user.edit') }}">登録情報の変更</a>
                        <a class="dropdown-item" href="{{ route('user.delete') }}">アカウントの削除</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                            ログアウト
                        </a>
                    </div>
                </li>
            @else
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline">
                            メニュー
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('login') }}">ログイン</a>
                        <a class="dropdown-item" href="{{ route('register') }}">ユーザー登録</a>
                    </div>
                </li>
            @endauth
        </ul>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div><!-- /.container-fluid -->
</nav>

@yield('content')

@auth
    @if (Gate::forUser(Auth::user())->allows('use-all-features'))
        <div class="spnav-space"></div>
        <nav class="spnav d-block d-lg-none">
            <ul class="spnav-list">
                @yield('global_nav')
            </ul>
        </nav>
    @endif
@endauth

</body>
</html>
