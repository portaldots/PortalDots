@extends('layouts.app')

@section('title', 'スタッフモード')

@section('content')
    <app-container medium>
        <list-view>
            <list-view-action-btn href="{{ route('home') }}">
                一般モードへ
            </list-view-action-btn>
        </list-view>
        <list-view>
            <template v-slot:title>メニュー</template>
            <list-view-item href="{{ route('staff.users.index') }}">
                <template v-slot:title>
                    <i class="far fa-address-book fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm">ユーザー情報管理</span>
                </template>
            </list-view-item>
            <list-view-item href="{{ route('staff.circles.index') }}">
                <template v-slot:title>
                    <i class="fas fa-star fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm">企画情報管理</span>
                </template>
            </list-view-item>
            <list-view-item href="{{ route('staff.tags.index') }}">
                <template v-slot:title>
                    <i class="fas fa-tags fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm">企画タグ管理</span>
                </template>
            </list-view-item>
            <list-view-item href="{{ route('staff.places.index') }}">
                <template v-slot:title>
                    <i class="fas fa-store fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm">場所情報管理</span>
                </template>
            </list-view-item>
            <list-view-item href="{{ route('staff.pages.index') }}">
                <template v-slot:title>
                    <i class="fas fa-bullhorn fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm">お知らせ管理</span>
                </template>
            </list-view-item>
            <list-view-item href="{{ route('staff.documents.index') }}">
                <template v-slot:title>
                    <i class="far fa-file-alt fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm">配布資料管理</span>
                </template>
            </list-view-item>
            <list-view-item href="{{ url('/home_staff/applications') }}" data-turbolinks="false">
                <template v-slot:title>
                    <i class="far fa-edit fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm">申請管理</span>
                </template>
            </list-view-item>
            <list-view-item href="{{ route('staff.schedules.index') }}">
                <template v-slot:title>
                    <i class="far fa-calendar-alt fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm">スケジュール管理</span>
                </template>
            </list-view-item>
            <list-view-item href="{{ route('staff.contacts.categories.index') }}">
                <template v-slot:title>
                    <i class="fas fa-at fa-lg text-muted fa-fw"></i>
                    <span class="px-spacing-sm">お問い合わせ受付設定</span>
                </template>
            </list-view-item>
            @if (Auth::user()->is_admin)
                <list-view-item href="{{ route('admin.portal.edit') }}">
                    <template v-slot:title>
                        <i class="fas fa-cog fa-lg text-muted fa-fw"></i>
                        <span class="px-spacing-sm">
                            ポータル情報設定
                            <app-badge danger>管理者</app-badge>
                        </span>
                    </template>
                </list-view-item>
            @endif
        </list-view>
    </app-container>
@endsection
