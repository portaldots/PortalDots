@extends('layouts.app')

@section('title', 'スタッフモード')

@section('content')
    @unless ($hasSentEmail)
        <top-alert type="danger" keep-visible>
            <template v-slot:title>
                メールの一斉配信に失敗しました
            </template>
            CRON が適切に設定されているかご確認ください
        </top-alert>
    @endunless
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
            @if (!Auth::user()->is_admin && Auth::user()->permissions->isEmpty())
                <list-view-card>
                    <list-view-empty icon-class="fas fa-lock" text="管理者にアクセス権の付与を依頼してください">
                        <p>
                            スタッフモードには以下のような機能がありますが、アクセス権がないため利用できません。<br />
                            利用したい機能がある場合、{{ config('app.name') }}の管理者へお問い合わせください。
                        </p>
                        <p>
                            管理者の方へ : スタッフユーザーへのアクセス権は「スタッフの権限設定」で付与できます。
                        </p>
                    </list-view-empty>
                </list-view-card>
            @endif
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.users.read') ? ("'" . route('staff.users.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="far fa-address-book fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.users.read') ? '' : 'text-muted' }}">ユーザー情報管理</span>
                    @cannot('staff.users.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.circles.read') ? ("'" . route('staff.circles.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-star fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.circles.read') ? '' : 'text-muted' }}">企画情報管理</span>
                    @cannot('staff.circles.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.tags.read') ? ("'" . route('staff.tags.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-tags fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.tags.read') ? '' : 'text-muted' }}">企画タグ管理</span>
                    @cannot('staff.tags.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.places.read') ? ("'" . route('staff.places.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-store fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.places.read') ? '' : 'text-muted' }}">場所情報管理</span>
                    @cannot('staff.places.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.pages.read') ? ("'" . route('staff.pages.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-bullhorn fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.pages.read') ? '' : 'text-muted' }}">お知らせ管理</span>
                    @cannot('staff.pages.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.documents.read') ? ("'" . route('staff.documents.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="far fa-file-alt fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.documents.read') ? '' : 'text-muted' }}">配布資料管理</span>
                    @cannot('staff.documents.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.forms.read') ? ("'" . route('staff.forms.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="far fa-edit fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.forms.read') ? '' : 'text-muted' }}">申請管理</span>
                    @cannot('staff.forms.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.schedules.read') ? ("'" . route('staff.schedules.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="far fa-calendar-alt fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.schedules.read') ? '' : 'text-muted' }}">スケジュール管理</span>
                    @cannot('staff.schedules.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.contacts.categories.read') ? ("'" . route('staff.contacts.categories.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-at fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.contacts.categories.read') ? '' : 'text-muted' }}">お問い合わせ受付設定</span>
                    @cannot('staff.contacts.categories.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            <list-view-item v-bind:href="{{ Auth::user()->can('staff.permissions.read') ? ("'" . route('staff.permissions.index') . "'") : 'undefined' }}">
                <template v-slot:title>
                    <i class="fas fa-key fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm {{ Auth::user()->can('staff.permissions.read') ? '' : 'text-muted' }}">スタッフの権限設定</span>
                    @cannot('staff.permissions.read')
                        <br><small class="text-muted">この機能を利用するための権限がありません。アクセスする必要がある場合は、{{ config('app.name') }}の管理者へお問い合わせください。</small>
                    @endcannot
                </template>
            </list-view-item>
            @if (Auth::user()->is_admin)
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
