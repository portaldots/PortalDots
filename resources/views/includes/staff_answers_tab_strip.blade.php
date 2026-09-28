@if (!isset($form->participationType))
    <div class="tab_strip">
        @if (config('portal.form_editor.show_answers_tab'))
            <a href="{{ route('staff.forms.answers.index', ['form' => $form]) }}"
                class="tab_strip-tab{{ Route::currentRouteName() === 'staff.forms.answers.index' ? ' is-active' : '' }}">
                回答
            </a>
        @endif
        <a href="{{ route('staff.forms.editor', ['form' => $form]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'staff.forms.editor' ? ' is-active' : '' }}">
            {{ config('portal.form_editor.editor_label') }}
        </a>
        <a href="{{ route(config('portal.form_editor.settings_route'), ['form' => $form]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === config('portal.form_editor.settings_route') ? ' is-active' : '' }}">
            {{ config('portal.form_editor.settings_label') }}
        </a>
    </div>
@endif
