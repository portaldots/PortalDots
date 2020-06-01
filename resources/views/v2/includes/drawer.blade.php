<a class="drawer-header" href="{{ route('home') }}">
    {{ config('app.name') }}
</a>
<nav class="drawer-nav">
    @if (Auth::check() && Auth::user()->is_staff)
        <div class="px-spacing py-spacing">
            <a href="/home_staff" class="btn is-primary is-block" data-turbolinks="false">
                スタッフモードへ
            </a>
        </div>
    @endif
    <a href="{{ route('home') }}" class="drawer-nav__link{{ Request::is('/') ? ' is-active' : '' }}">
        <i class="fas fa-home drawer-nav__icon fa-fw"></i>
        ホーム
    </a>
    <a href="{{ route('pages.index') }}" class="drawer-nav__link{{ Request::is('pages*') ? ' is-active' : '' }}">
        <i class="fas fa-bullhorn drawer-nav__icon fa-fw"></i>
        お知らせ
    </a>
    <a href="{{ route('documents.index') }}"
        class="drawer-nav__link{{ Request::is('documents*') ? ' is-active' : '' }}">
        <i class="far fa-file-alt drawer-nav__icon fa-fw"></i>
        配布資料
    </a>
    @auth
        <a href="{{ route('forms.index') }}" class="drawer-nav__link{{ Request::is('forms*') ? ' is-active' : '' }}">
            <i class="far fa-edit drawer-nav__icon fa-fw"></i>
            申請
        </a>
    @endauth
    <a href="{{ route('schedules.index') }}"
        class="drawer-nav__link{{ Request::is('schedules*') ? ' is-active' : '' }}">
        <i class="far fa-calendar-alt drawer-nav__icon fa-fw"></i>
        スケジュール
    </a>
    @auth
        <a href="{{ route('contacts') }}" class="drawer-nav__link{{ Request::is('contacts*') ? ' is-active' : '' }}">
            <i class="far fa-envelope drawer-nav__icon fa-fw"></i>
            お問い合わせ
        </a>
        <a href="{{ route('user.edit') }}" class="drawer-nav__link{{ Request::is('user*') ? ' is-active' : '' }}">
            <i class="fas fa-cog drawer-nav__icon fa-fw"></i>
            ユーザー設定
        </a>
    @endauth
</nav>
<div class="drawer-adj">
    <div class="drawer-user">
        @auth
            <div class="drawer-user__info">
                <div>{{ Auth::user()->name }}としてログイン中</div>
                @if (Auth::user()->is_staff)
                    <div>
                        <app-badge primary>スタッフ</app-badge>
                        @if (Auth::user()->is_admin)
                            <app-badge danger>管理者</app-badge>
                        @endif
                    </div>
                @endif
            </div>
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
            <a href="{{ route('login') }}" class="btn is-primary is-block">
                <strong>ログイン</strong>
            </a>
        @endauth
    </div>
</div>
