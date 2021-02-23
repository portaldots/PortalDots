@inject('selectorService', 'App\Services\Circles\SelectorService')
@inject('readsService', 'App\Services\Pages\ReadsService')

@staffpage
    @if (Auth::check() && Auth::user()->is_staff)
        <a class="drawer-header" href="/home_staff" data-turbolinks="false">
            {{ config('app.name') }}
            <app-badge primary>スタッフモード</app-badge>
        </a>
        <nav class="drawer-nav">
            <div class="px-spacing py-spacing">
                <a href="/" class="btn is-primary is-block">
                    一般モードへ
                </a>
            </div>
            <a href="{{ route('staff.index') }}" class="drawer-nav__link{{ Request::is('staff') ? ' is-active' : '' }}">
                <i class="fas fa-home drawer-nav__icon fa-fw"></i>
                スタッフモード ホーム
            </a>
            <a href="{{ route('staff.users.index') }}" class="drawer-nav__link{{ Request::is('staff/users*') ? ' is-active' : '' }}">
                <i class="far fa-address-book drawer-nav__icon fa-fw"></i>
                ユーザー情報管理
            </a>
            <a href="{{ route('staff.circles.index') }}" class="drawer-nav__link{{ Request::is('staff/circles*') ? ' is-active' : '' }}">
                <i class="fas fa-star drawer-nav__icon fa-fw"></i>
                企画情報管理
            </a>
            <a href="{{ route('staff.tags.index') }}" class="drawer-nav__link{{ Request::is('staff/tags*') ? ' is-active' : '' }}">
                <i class="fas fa-tags drawer-nav__icon fa-fw"></i>
                企画タグ管理
            </a>
            <a href="{{ route('staff.places.index') }}" class="drawer-nav__link{{ Request::is('staff/places*') ? ' is-active' : '' }}">
                <i class="fas fa-store drawer-nav__icon fa-fw"></i>
                場所情報管理
            </a>
            <a href="{{ route('staff.pages.index') }}" class="drawer-nav__link{{ Request::is('staff/pages*') ? ' is-active' : '' }}">
                <i class="fas fa-bullhorn drawer-nav__icon fa-fw"></i>
                お知らせ管理
            </a>
            <a href="{{ route('staff.documents.index') }}" class="drawer-nav__link{{ Request::is('staff/documents*') ? ' is-active' : '' }}">
                <i class="far fa-file-alt drawer-nav__icon fa-fw"></i>
                配布資料管理
            </a>
            <a href="{{ url('/home_staff/applications') }}" class="drawer-nav__link{{ Request::is('staff/forms*') ? ' is-active' : '' }}" data-turbolinks="false">
                <i class="far fa-edit drawer-nav__icon fa-fw"></i>
                申請管理
            </a>
            <a href="{{ route('staff.schedules.index') }}" class="drawer-nav__link{{ Request::is('staff/schedules*') ? ' is-active' : '' }}">
                <i class="far fa-calendar-alt drawer-nav__icon fa-fw"></i>
                スケジュール管理
            </a>
            <a href="{{ route('staff.contacts.categories.index') }}" class="drawer-nav__link{{ Request::is('staff/contacts/categories*') ? ' is-active' : '' }}">
                <i class="fas fa-at drawer-nav__icon fa-fw"></i>
                お問い合わせ受付設定
            </a>
            @if (Auth::user()->is_admin)
                <a href="{{ route('admin.portal.edit') }}" class="drawer-nav__link{{ Request::is('admin/portal*') ? ' is-active' : '' }}">
                    <i class="fas fa-cog drawer-nav__icon fa-fw"></i>
                    ポータル情報設定
                    <app-badge danger>管理者</app-badge>
                </a>
            @endif
        </nav>
    @endif
@else
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
            @if ($readsService->getUnreadsCountOnSelectedCircle() > 0)
                <app-badge primary pill strong class="drawer-nav__badge">
                    {{ $readsService->getUnreadsCountOnSelectedCircle() }}
                </app-badge>
            @endif
        </a>
        <a href="{{ route('documents.index') }}"
            class="drawer-nav__link{{ Request::is('documents*') ? ' is-active' : '' }}">
            <i class="far fa-file-alt drawer-nav__icon fa-fw"></i>
            配布資料
        </a>
        @if (Auth::check() && !empty($selectorService->getCircle()))
            <a href="{{ route('forms.index') }}" class="drawer-nav__link{{ Request::is('forms*') ? ' is-active' : '' }}">
                <i class="far fa-edit drawer-nav__icon fa-fw"></i>
                申請
            </a>
        @endif
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
@endstaffpage
