<div class="tab_strip">
    <a
        href="{{ route('user.edit') }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'user.edit' ? ' is-active' : '' }}"
    >
        一般
    </a>
    <a
        href="{{ route('change_password') }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'change_password' ? ' is-active' : '' }}"
    >
        パスワード
    </a>
    <a
        href="{{ route('user.delete') }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'user.delete' ? ' is-active' : '' }}"
    >
        退会
    </a>
</div>
