@php
$step = 0;
if (Route::currentRouteName() === 'circles.create' || Route::currentRouteName() === 'circles.edit') {
$step = 1;
} elseif (Route::currentRouteName() === 'circles.users.index') {
$step = 2;
} elseif (Route::currentRouteName() === 'circles.confirm') {
$step = 3;
}
@endphp

<app-header container-medium>
    <template v-slot:title>
        企画参加登録
        <small class="text-muted">(ステップ {{ $step }} / 3)</small>
    </template>
    @isset ($circle)
        <p class="text-muted">
            {{ $circle->name }}
        </p>
    @endisset
    <steps-list>
        <steps-list-item {{ $step === 1 ? 'active' : '' }}>企画情報</steps-list-item>
        <steps-list-item {{ $step === 2 ? 'active' : '' }}>メンバー</steps-list-item>
        <steps-list-item {{ $step === 3 ? 'active' : '' }}>提出</steps-list-item>
    </steps-list>
</app-header>
