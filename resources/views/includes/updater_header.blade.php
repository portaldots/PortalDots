@php
$step = 0;
if (Route::currentRouteName() === 'admin.update.index') {
$step = 1;
} elseif (Route::currentRouteName() === 'admin.update.before-update') {
$step = 2;
} elseif (Route::currentRouteName() === 'admin.update.last-step') {
$step = 3;
}
@endphp

<app-header container-medium>
    <template v-slot:title>
        PortalDots のアップデート
        @if ($step > 0)
            <small class="text-muted">(ステップ {{ $step }} / 3)</small>
        @endif
    </template>
    @if ($step > 0)
        <steps-list>
            <steps-list-item {{ $step === 1 ? 'active' : '' }}>ようこそ</steps-list-item>
            <steps-list-item {{ $step === 2 ? 'active' : '' }}>アップデートの前に</steps-list-item>
            <steps-list-item {{ $step === 3 ? 'active' : '' }}>アップデート</steps-list-item>
        </steps-list>
    @endif
</app-header>
