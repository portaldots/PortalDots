<a class="drawer-header" href="{{ url('/') }}">
    {{ config('app.name') }}
</a>
<nav class="drawer-nav">
    <ul class="drawer-nav__list">
        <li class="drawer-nav__item">
            {{-- TODO: Request::is の引数は将来的に '' (空文字) にしたい --}}
            <a href="{{ url('/') }}" class="drawer-nav__link{{ Request::is('login') || Request::is('home*') ? ' is-active' : '' }}">
                ホーム
            </a>
        </li>
        <li class="drawer-nav__item">
            <a href="{{ route('pages.index') }}" class="drawer-nav__link{{ Request::is('pages*') ? ' is-active' : '' }}">
                お知らせ
            </a>
        </li>
        <li class="drawer-nav__item">
            <a href="{{ route('documents.index') }}" class="drawer-nav__link{{ Request::is('documents*') ? ' is-active' : '' }}">
                配布資料
            </a>
        </li>
        @auth
        <li class="drawer-nav__item">
            <a href="{{ url('home/applications') }}" class="drawer-nav__link">
                申請
            </a>
        </li>
        @endauth
        <li class="drawer-nav__item">
            <a href="{{ route('schedules.index') }}" class="drawer-nav__link{{ Request::is('schedules*') ? ' is-active' : '' }}">
                スケジュール
            </a>
        </li>
        @auth
        <li class="drawer-nav__item">
            <a href="{{ route('contacts') }}" class="drawer-nav__link{{ Request::is('contacts*') ? ' is-active' : '' }}">
                お問い合わせ
            </a>
        </li>
        <li class="drawer-nav__item">
            <a href="{{ route('user.edit') }}" class="drawer-nav__link">
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
    <a
        href="{{ route('logout') }}"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        class="btn is-secondary is-block is-no-border"
    >
        ログアウト
    </a>
    @else
    <p class="drawer-user__info">
        ログインしていません
    </p>
    <a href="{{ url('/') }}" class="btn is-secondary is-block is-no-border">
        <strong>ログイン</strong>
    </a>
    @endauth
    <form id="logout-form" action="{{ route('logout') }}" method="post">
        @csrf
    </form>
</div>
