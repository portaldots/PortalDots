@inject('menuRegistry', 'App\Services\Navigation\MenuRegistry')

@staffpage
    @if (Auth::check() && Auth::user()->is_staff)
        <a class="drawer-header" href="{{ route('staff.index') }}">
            {{ config('app.name') }}
            <app-badge primary>スタッフモード</app-badge>
            @if (config('portal.enable_demo_mode'))
                <br /><small class="text-muted">デモサイト</small>
            @endif
        </a>
        <nav class="drawer-nav">
            <div class="px-spacing py-spacing">
                <a href="/" class="btn is-primary is-block">
                    一般モードへ
                </a>
            </div>
            @foreach ($menuRegistry->get('staff') as $item)
                <a href="{{ $item->href() }}" class="drawer-nav__link{{ $item->isActive() ? ' is-active' : '' }}">
                    <i class="{{ $item->icon }} drawer-nav__icon fa-fw"></i>
                    {{ $item->label() }}
                </a>
            @endforeach
            @foreach ($menuRegistry->get('admin') as $item)
                <a href="{{ $item->href() }}" class="drawer-nav__link{{ $item->isActive() ? ' is-active' : '' }}">
                    <i class="{{ $item->icon }} drawer-nav__icon fa-fw"></i>
                    {{ $item->label() }}
                    <app-badge danger>管理者</app-badge>
                </a>
            @endforeach
        </nav>
    @endif
@else
    <a class="drawer-header" href="{{ route('home') }}">
        {{ config('app.name') }}
        @if (config('portal.enable_demo_mode'))
            <br /><small class="text-muted">デモサイト</small>
        @endif
    </a>
    <nav class="drawer-nav">
        @if (Auth::check() && Auth::user()->is_staff)
            <div class="px-spacing py-spacing">
                <a href="{{ route('staff.index') }}" class="btn is-primary is-block">
                    スタッフモードへ
                </a>
            </div>
        @endif
        @foreach ($menuRegistry->get('circle') as $item)
            <a href="{{ $item->href() }}" class="drawer-nav__link{{ $item->isActive() ? ' is-active' : '' }}">
                <i class="{{ $item->icon }} drawer-nav__icon fa-fw"></i>
                {{ $item->label() }}
                @if ($item->badgeCount())
                    <app-badge primary pill strong class="drawer-nav__badge">
                        {{ $item->badgeCount() }}
                    </app-badge>
                @endif
            </a>
        @endforeach
    </nav>
@endstaffpage

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
            <a href="{{ route('login') }}" class="btn is-primary is-block">
                <strong>ログイン</strong>
            </a>
        @endauth
    </div>
</div>
