<a class="drawer-header" href="{{ url('/') }}">
    {{ config('app.name') }}
</a>
<nav class="drawer-nav">
    <ul class="drawer-nav__list">
        @auth
        {{-- TODO: Request::is の引数は将来的に '' (空文字) にしたい --}}
        <li class="drawer-nav__item">
            {{-- TODO: Request::is の引数は将来的に '' (空文字) にしたい --}}
            <a href="{{ route('home') }}" class="drawer-nav__link{{ Request::is('login') || Request::is('home*') ? ' is-active' : '' }}">
                <i class="fas fa-home drawer-nav__icon fa-fw"></i>
                ホーム
            </a>
        </li>
        @else
        {{-- TODO: Request::is の引数は将来的に '' (空文字) にしたい --}}
        <li class="drawer-nav__item">
            {{-- TODO: Request::is の引数は将来的に '' (空文字) にしたい --}}
            <a href="{{ route('login') }}?new=1" class="drawer-nav__link{{ Request::is('login') || Request::is('home*') ? ' is-active' : '' }}">
                <i class="fas fa-home drawer-nav__icon fa-fw"></i>
                ホーム
            </a>
        </li>
        @endauth
        <li class="drawer-nav__item">
            <a href="{{ route('pages.index') }}" class="drawer-nav__link{{ Request::is('pages*') ? ' is-active' : '' }}">
                <i class="fas fa-bullhorn drawer-nav__icon fa-fw"></i>
                お知らせ
            </a>
        </li>
        <li class="drawer-nav__item">
            <a href="{{ route('documents.index') }}" class="drawer-nav__link{{ Request::is('documents*') ? ' is-active' : '' }}">
                <i class="far fa-file-alt drawer-nav__icon fa-fw"></i>
                配布資料
            </a>
        </li>
        @auth
        <li class="drawer-nav__item">
            {{-- 申請ページが Project v2 になったら、data-turbolinks="false" は削除する --}}
            <a href="{{ url('home/applications') }}" class="drawer-nav__link" data-turbolinks="false">
                <i class="far fa-edit drawer-nav__icon fa-fw"></i>
                申請
            </a>
        </li>
        @endauth
        <li class="drawer-nav__item">
            <a href="{{ route('schedules.index') }}" class="drawer-nav__link{{ Request::is('schedules*') ? ' is-active' : '' }}">
                <i class="far fa-calendar-alt drawer-nav__icon fa-fw"></i>
                スケジュール
            </a>
        </li>
        @auth
        <li class="drawer-nav__item">
            <a href="{{ route('contacts') }}" class="drawer-nav__link{{ Request::is('contacts*') ? ' is-active' : '' }}">
                <i class="far fa-envelope drawer-nav__icon fa-fw"></i>
                お問い合わせ
            </a>
        </li>
        <li class="drawer-nav__item">
            <a href="{{ route('user.edit') }}" class="drawer-nav__link{{ Request::is('user*') || Request::is('change_password') ? ' is-active' : '' }}">
                <i class="fas fa-cog drawer-nav__icon fa-fw"></i>
                ユーザー設定
            </a>
        </li>
        @endauth
    </ul>
</nav>
<div class="drawer-user">
    @auth
    <p class="drawer-user__info">
        {{ Auth::user()->name }}としてログイン中
    </p>
    <form action="{{ route('logout') }}" method="post">
        @csrf
        <button type="submit" class="btn is-secondary is-block">
            ログアウト
        </button>
    </form>
    @else
    <p class="drawer-user__info">
        ログインしていません
    </p>
    <a href="{{ route('login') }}?new=1" class="btn is-primary is-block">
        <strong>ログイン</strong>
    </a>
    @endauth
</div>
