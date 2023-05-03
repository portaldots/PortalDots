@if (!isset($form->participationType))
    <div class="tab_strip">
        <a href="{{ route('staff.forms.answers.index', ['form' => $form]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'staff.forms.answers.index' ? ' is-active' : '' }}">
            回答
        </a>
        <a href="{{ route('staff.forms.editor', ['form' => $form]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'staff.forms.editor' ? ' is-active' : '' }}">
            エディター
        </a>
        <a href="{{ route('staff.forms.edit', ['form' => $form]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'staff.forms.edit' ? ' is-active' : '' }}">
            設定
        </a>
    </div>
@endif
