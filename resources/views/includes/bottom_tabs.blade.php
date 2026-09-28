@inject('menuRegistry', 'App\Services\Navigation\MenuRegistry')

<div class="bottom_tabs">
    <div class="bottom_tabs-container">
        @foreach ($menuRegistry->get('circle') as $item)
            @continue(!$item->showInBottomTabs)
            <a href="{{ $item->href() }}" class="bottom_tabs-tab{{ $item->isActive() ? ' is-active' : '' }}">
                <i class="{{ $item->icon }} bottom_tabs-tab__icon"></i>
                <div class="bottom_tabs-tab__label">
                    @if ($item->badgeCount())
                        <i class="fas fa-circle bottom_tabs-tab__notifier"></i>
                    @endif
                    {{ $item->label() }}
                </div>
            </a>
        @endforeach
    </div>
</div>
