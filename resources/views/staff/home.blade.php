@extends('layouts.app')

@section('title', 'スタッフモード')

@section('content')
    @include('includes.staff_home_tab_strip')
    @unless ($hasSentEmail)
        <top-alert type="danger" container-medium keep-visible>
            <template v-slot:title>
                メールの一斉配信に失敗しました
            </template>
            CRON が適切に設定されているかご確認ください
        </top-alert>
    @endunless
    <app-container fluid>
        <div class="pb-spacing"></div>
        @if (!Auth::user()->is_admin && Auth::user()->permissions->isEmpty() && !config('portal.enable_demo_mode'))
            <list-view class="pb-spacing">
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
            </list-view>
        @endif

        @php
            $menu_items = [
                [
                    'can' => Auth::user()->can('staff.users.read'),
                    'href' => route('staff.users.index'),
                    'icon_class' => 'far fa-address-book fa-fw',
                    'title' => 'ユーザー情報管理',
                    'description' => config('app.name') . 'に登録しているユーザーの情報を管理します',
                ],
                [
                    'can' => Auth::user()->can('staff.groups.read'),
                    'href' => route('staff.groups.index'),
                    'icon_class' => 'fas fa-users fa-fw',
                    'title' => '団体管理',
                    'description' => config('app.name') . 'に登録している団体の情報を管理します',
                ],
                [
                    'can' => Auth::user()->can('staff.circles.read'),
                    'href' => route('staff.circles.index'),
                    'icon_class' => 'fas fa-star fa-fw',
                    'title' => '企画情報管理',
                    'description' => config('app.name') . 'に登録している企画の情報の管理や、企画参加登録フォームの設定を行います',
                ],
                [
                    'can' => Auth::user()->can('staff.tags.read'),
                    'href' => route('staff.tags.index'),
                    'icon_class' => 'fas fa-tags fa-fw',
                    'title' => '企画タグ管理',
                    'description' => '企画を分類するためのタグを管理します',
                ],
                [
                    'can' => Auth::user()->can('staff.places.read'),
                    'href' => route('staff.places.index'),
                    'icon_class' => 'fas fa-store fa-fw',
                    'title' => '場所情報管理',
                    'description' => '企画が利用できる場所の情報を管理します',
                ],
                [
                    'can' => Auth::user()->can('staff.pages.read'),
                    'href' => route('staff.pages.index'),
                    'icon_class' => 'fas fa-bullhorn fa-fw',
                    'title' => 'お知らせ管理',
                    'description' => config('app.name') . '上に表示するお知らせを管理します。お知らせはメールで一斉配信できます',
                ],
                [
                    'can' => Auth::user()->can('staff.documents.read'),
                    'href' => route('staff.documents.index'),
                    'icon_class' => 'far fa-file-alt fa-fw',
                    'title' => '配布資料管理',
                    'description' => config('app.name') . '上で配布する資料(ファイル)を管理します',
                ],
                [
                    'can' => Auth::user()->can('staff.forms.read'),
                    'href' => route('staff.forms.index'),
                    'icon_class' => 'far fa-edit fa-fw',
                    'title' => '申請管理',
                    'description' => '各企画から受け付ける申請フォームの作成や、提出された申請の確認を行います',
                ],
                [
                    'can' => Auth::user()->can('staff.contacts.categories.read'),
                    'href' => route('staff.contacts.categories.index'),
                    'icon_class' => 'fas fa-at fa-fw',
                    'title' => 'お問い合わせ受付設定',
                    'description' => config('app.name') . 'のお問い合わせフォームの受付方法を設定します',
                ],
                [
                    'can' => Auth::user()->can('staff.permissions.read'),
                    'href' => route('staff.permissions.index'),
                    'icon_class' => 'fas fa-key fa-fw',
                    'title' => 'スタッフの権限設定',
                    'description' => 'スタッフモードで利用可能な機能を、スタッフごとに制限できます',
                ],
                [
                    'can' => Auth::user()->is_admin,
                    'admin' => true,
                    'href' => route('admin.activity_log.index'),
                    'icon_class' => 'fas fa-user-edit fa-fw',
                    'title' => 'アクティビティログ',
                    'description' => config('app.name') . '内で行われた各種データ操作の履歴を確認します',
                ],
                [
                    'can' => Auth::user()->is_admin,
                    'admin' => true,
                    'href' => route('admin.portal.edit'),
                    'icon_class' => 'fas fa-cog fa-fw',
                    'title' => 'PortalDots の設定',
                    'description' => 'このウェブシステムの設定を変更します',
                ],
                [
                    'can' => true,
                    'href' => route('staff.about'),
                    'icon_class' => 'fa-solid fa-arrows-rotate fa-fw',
                    'title' => 'PortalDots のアップデートの確認',
                    'description' => 'セキュリティのため、定期的に PortalDots をアップデートしましょう',
                ],
            ];
        @endphp

        <layout-row grid-template-columns="repeat(auto-fit, minmax(320px, 1fr))" no-responsive>
            @foreach ($menu_items as $item)
                @if (isset($item['admin']) && !Auth::user()->is_admin)
                    @continue
                @endif
                <layout-column>
                    <card-link v-bind:href="{{ $item['can'] ? "'" . $item['href'] . "'" : 'undefined' }}">
                        <template v-slot:icon>
                            <i class="{{ $item['icon_class'] }}"></i>
                        </template>
                        <template v-slot:title>
                            {{ $item['title'] }}
                            @if (isset($item['admin']))
                                <app-badge danger>管理者</app-badge>
                            @endif
                        </template>
                        <template v-slot:description>
                            @if (!$item['can'])
                                この機能を利用するための権限がありません
                            @elseif (isset($item['description']))
                                {{ $item['description'] }}
                            @endif
                        </template>
                    </card-link>
                </layout-column>
            @endforeach
        </layout-row>
    </app-container>
@endsection
