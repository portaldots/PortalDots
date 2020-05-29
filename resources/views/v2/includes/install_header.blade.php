@php
$step = 0;
if (Route::currentRouteName() === 'install.portal.edit') {
$step = 1;
} elseif (Route::currentRouteName() === 'install.database.edit') {
$step = 2;
} elseif (Route::currentRouteName() === 'install.mail.edit' || Route::currentRouteName() === 'install.mail.test') {
$step = 3;
} elseif (Route::currentRouteName() === 'install.admin.create') {
$step = 4;
}
@endphp

<app-header container-medium>
    <template v-slot:title>
        PortalDots のインストール
        @if ($step > 0)
            <small class="text-muted">(ステップ {{ $step }} / 4)</small>
        @endif
    </template>
    @if ($step > 0)
        <steps-list>
            <steps-list-item {{ $step === 1 ? 'active' : '' }}>ポータル情報</steps-list-item>
            <steps-list-item {{ $step === 2 ? 'active' : '' }}>データベース</steps-list-item>
            <steps-list-item {{ $step === 3 ? 'active' : '' }}>メール</steps-list-item>
            <steps-list-item {{ $step === 4 ? 'active' : '' }}>管理者</steps-list-item>
        </steps-list>
    @endif
</app-header>
