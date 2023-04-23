@if (!isset($form->customForm))
    <div class="tab_strip">
        <a href="{{ route('staff.forms.answers.index', ['form' => $form]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'staff.forms.answers.index' ? ' is-active' : '' }}">
            回答一覧
        </a>
        <a href="{{ route('staff.forms.edit', ['form' => $form]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'staff.forms.edit' ? ' is-active' : '' }}">
            フォーム設定
        </a>
        <a href="{{ route('staff.forms.editor', ['form' => $form]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'staff.forms.editor' ? ' is-active' : '' }}">
            フォームエディター
        </a>
    </div>
@endif
