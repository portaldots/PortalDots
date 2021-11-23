@inject('selectorService', 'App\Services\Circles\SelectorService')
@inject('readsService', 'App\Services\Pages\ReadsService')

<div class="bottom_tabs">
    <div class="bottom_tabs-container">
        <a href="{{ route('home') }}" class="bottom_tabs-tab{{ Request::is('/') ? ' is-active' : '' }}">
            <i class="fas fa-home bottom_tabs-tab__icon"></i>
            <div class="bottom_tabs-tab__label">ホーム</div>
        </a>
        <a href="{{ route('pages.index') }}" class="bottom_tabs-tab{{ Request::is('pages*') ? ' is-active' : '' }}">
            <i class="fas fa-bullhorn bottom_tabs-tab__icon"></i>
            <div class="bottom_tabs-tab__label">
                @if ($readsService->getUnreadsCountOnSelectedCircle() > 0)
                    <i class="fas fa-circle bottom_tabs-tab__notifier"></i>
                @endif
                お知らせ
            </div>
        </a>
        <a href="{{ route('documents.index') }}"
            class="bottom_tabs-tab{{ Request::is('documents*') ? ' is-active' : '' }}">
            <i class="far fa-file-alt bottom_tabs-tab__icon"></i>
            <div class="bottom_tabs-tab__label">配布資料</div>
        </a>
        @if (Auth::check() && !empty($selectorService->getCircle()))
            <a href="{{ route('forms.index') }}" class="bottom_tabs-tab{{ Request::is('forms*') ? ' is-active' : '' }}">
                <i class="far fa-edit bottom_tabs-tab__icon"></i>
                <div class="bottom_tabs-tab__label">申請</div>
            </a>
        @endif
        @auth
            <a href="{{ route('contacts') }}"
                class="bottom_tabs-tab{{ Request::is('contacts*') ? ' is-active' : '' }}">
                <i class="far fa-envelope bottom_tabs-tab__icon"></i>
                <div class="bottom_tabs-tab__label">お問い合わせ</div>
            </a>
        @endauth
    </div>
</div>
