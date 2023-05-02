<div class="tab_strip">
    <a href="{{ route('staff.circles.participation_types.index', ['participation_type' => $participation_type]) }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'staff.circles.participation_types.index' ? ' is-active' : '' }}">
        企画一覧
    </a>
    <a href="{{ route('staff.circles.participation_types.edit', ['participation_type' => $participation_type]) }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'staff.circles.participation_types.edit' ? ' is-active' : '' }}">
        設定
        @if (!$participation_type->form->is_public)
            <app-badge muted>参加登録フォーム非公開</app-badge>
        @elseif(!$participation_type->form->isOpen())
            <app-badge muted>参加登録受付期間外</app-badge>
        @else
            <app-badge primary strong>参加登録受付期間内</app-badge>
        @endif
    </a>
</div>
