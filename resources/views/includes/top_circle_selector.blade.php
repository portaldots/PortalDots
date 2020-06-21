@inject('selectorService', 'App\Services\Circles\SelectorService')

@auth
    @if (count($selectorService->getSelectableCirclesList(Auth::user(), Request::path())) >= 2 &&
        empty(trim($__env->yieldContent('no_circle_selector')) /* ← no_circle_selector という section がセットされていない場合 true */) &&
        !Request::is('staff*'))
        <circle-selector-dropdown
            v-bind:circles="{{ $selectorService->getJsonForCircleSelectorDropdown(Auth::user(), Request::path()) }}"
            v-bind:selected-circle-id="{{ $selectorService->getCircle()->id }}"
            selected-circle-name="{{ $selectorService->getCircle()->name }}"
            selected-circle-group-name="{{ $selectorService->getCircle()->group_name }}"
        ></circle-selector-dropdown>
    @endif
@endauth
