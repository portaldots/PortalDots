@section('no_circle_selector', true)

<div class="tab_strip">
    <a href="{{ route('user.edit') }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'user.edit' ? ' is-active' : '' }}">
        一般
    </a>
    <a href="{{ route('user.appearance') }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'user.appearance' ? ' is-active' : '' }}">
        外観
    </a>
    <a href="{{ route('user.password') }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'user.password' ? ' is-active' : '' }}">
        パスワード変更
    </a>
    <a href="{{ route('user.delete') }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'user.delete' ? ' is-active' : '' }}">
        アカウント削除
    </a>
</div>
