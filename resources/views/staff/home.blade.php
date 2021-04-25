@extends('layouts.app')

@section('title', 'スタッフモード')

@section('content')
    <app-container medium>
        <list-view>
            <list-view-action-btn href="{{ route('home') }}">
                一般モードへ
            </list-view-action-btn>
            <list-view-action-btn href="{{ route('staff.about') }}">
                PortalDots のアップデートの確認
            </list-view-action-btn>
        </list-view>
        <list-view>
            <template v-slot:title>メニュー</template>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.users') ? ("'" . route('staff.users.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="far fa-address-book fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.users') ? '' : 'text-muted' }}">ユーザー情報管理</span>
                    @cannot('staff.users')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.circles') ? ("'" . route('staff.circles.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-star fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.circles') ? '' : 'text-muted' }}">企画情報管理</span>
                    @cannot('staff.circles')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.tags') ? ("'" . route('staff.tags.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-tags fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.tags') ? '' : 'text-muted' }}">企画タグ管理</span>
                    @cannot('staff.tags')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.places') ? ("'" . route('staff.places.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-store fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.places') ? '' : 'text-muted' }}">場所情報管理</span>
                    @cannot('staff.places')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.pages') ? ("'" . route('staff.pages.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-bullhorn fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.pages') ? '' : 'text-muted' }}">お知らせ管理</span>
                    @cannot('staff.pages')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.documents') ? ("'" . route('staff.documents.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="far fa-file-alt fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.documents') ? '' : 'text-muted' }}">配布資料管理</span>
                    @cannot('staff.documents')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.forms') ? ("'" . route('staff.forms.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="far fa-edit fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.forms') ? '' : 'text-muted' }}">申請管理</span>
                    @cannot('staff.forms')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.schedules') ? ("'" . route('staff.schedules.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="far fa-calendar-alt fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.schedules') ? '' : 'text-muted' }}">スケジュール管理</span>
                    @cannot('staff.schedules')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.contacts') ? ("'" . route('staff.contacts.categories.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-at fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.contacts') ? '' : 'text-muted' }}">お問い合わせ受付設定</span>
                    @cannot('staff.contacts')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            @if (Auth::user()->is_admin)
                <list-view-item href="{{ route('admin.permissions.index') }}">
                    <template v-slot:title>
                        <i class="fas fa-key fa-lg text-muted fa-fw"></i>
                        <span class="px-spacing-sm">
                            スタッフの権限設定
                            <app-badge danger>管理者</app-badge>
                        </span>
                    </template>
                </list-view-item>
                <list-view-item href="{{ route('admin.portal.edit') }}">
                    <template v-slot:title>
                        <i class="fas fa-cog fa-lg text-muted fa-fw"></i>
                        <span class="px-spacing-sm">
                            PortalDots の設定
                            <app-badge danger>管理者</app-badge>
                        </span>
                    </template>
                </list-view-item>
            @endif
        </list-view>
    </app-container>
@endsection
