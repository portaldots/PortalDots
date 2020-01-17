<div class="bottom_tabs">
    <div class="bottom_tabs-container">
        @auth
        {{-- TODO: Request::is の引数は将来的に '' (空文字) にしたい --}}
        <a href="{{ route('home') }}" class="bottom_tabs-tab{{ Request::is('login') || Request::is('home*') ? ' is-active' : '' }}">
            <i class="fas fa-home bottom_tabs-tab__icon"></i>
            <div class="bottom_tabs-tab__label">ホーム</div>
        </a>
        @else
        {{-- TODO: Request::is の引数は将来的に '' (空文字) にしたい --}}
        <a href="{{ route('login') }}?new=1" class="bottom_tabs-tab{{ Request::is('login') || Request::is('home*') ? ' is-active' : '' }}">
            <i class="fas fa-home bottom_tabs-tab__icon"></i>
            <div class="bottom_tabs-tab__label">ホーム</div>
        </a>
        @endauth
        <a href="{{ route('pages.index') }}" class="bottom_tabs-tab{{ Request::is('pages*') ? ' is-active' : '' }}">
            <i class="fas fa-bullhorn bottom_tabs-tab__icon"></i>
            <div class="bottom_tabs-tab__label">お知らせ</div>
        </a>
        <a href="{{ route('documents.index') }}" class="bottom_tabs-tab{{ Request::is('documents*') ? ' is-active' : '' }}">
            <i class="far fa-file-alt bottom_tabs-tab__icon"></i>
            <div class="bottom_tabs-tab__label">配布資料</div>
        </a>
        @auth
        <a href="{{ url('home/applications') }}" class="bottom_tabs-tab">
            <i class="far fa-edit bottom_tabs-tab__icon"></i>
            <div class="bottom_tabs-tab__label">申請</div>
        </a>
        @endauth
        <a href="{{ route('schedules.index') }}" class="bottom_tabs-tab{{ Request::is('schedules*') ? ' is-active' : '' }}">
            <i class="far fa-calendar-alt bottom_tabs-tab__icon"></i>
            <div class="bottom_tabs-tab__label">予定</div>
        </a>
    </div>
</div>
