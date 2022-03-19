@if (Auth::check() && Auth::user()->is_staff)
    <div class="tab_strip">
        <a href="{{ route('home') }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'home' ? ' is-active' : '' }}">
            一般モード
        </a>
        <a href="{{ route('staff.index') }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'staff.index' ? ' is-active' : '' }}">
            スタッフモード
        </a>
    </div>
@endif
