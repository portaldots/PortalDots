<div class="tab_strip">
    <a href="{{ route('staff.circles.participation_types.index', ['participation_type' => $participation_type]) }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'staff.circles.participation_types.index' ? ' is-active' : '' }}">
        企画一覧
    </a>
    <a href="{{ route('staff.circles.participation_types.edit', ['participation_type' => $participation_type]) }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'staff.circles.participation_types.edit' ? ' is-active' : '' }}">
        参加種別を編集
    </a>
    <a href="{{ route('staff.circles.participation_types.form.edit', ['participation_type' => $participation_type]) }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'staff.circles.participation_types.form.edit' ? ' is-active' : '' }}">
        参加登録フォームの設定
        @if (!$participation_type->form->is_public)
            <app-badge muted>非公開</app-badge>
        @elseif(!$participation_type->form->isOpen())
            <app-badge muted>受付期間外</app-badge>
        @else
            <app-badge primary strong>受付期間内</app-badge>
        @endif
    </a>
</div>
