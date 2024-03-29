@extends('layouts.app')

@prepend('meta')
    <meta name="description" content="{{ config('portal.description') }}">
@endprepend

@section('content')
    @auth
        @unless (Auth::user()->areBothEmailsVerified())
            <top-alert type="primary" keep-visible>
                <template v-slot:title>
                    <i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i>
                    メール認証を行ってください
                </template>

                {{ config('app.name') }}の全機能を利用するには、次のメールアドレス宛に送信された確認メール内のURLにアクセスしてください。
                <strong>
                    @unless (Auth::user()->hasVerifiedUnivemail())
                        {{ Auth::user()->univemail }}
                        @unless (Auth::user()->hasVerifiedEmail())
                            •
                        @endunless
                    @endunless
                    @unless (Auth::user()->hasVerifiedEmail())
                        {{ Auth::user()->email }}
                    @endunless
                </strong>

                <template v-slot:cta>
                    <a href="{{ route('verification.notice') }}" class="btn is-primary-inverse is-no-border is-wide">
                        <strong>もっと詳しく</strong>
                    </a>
                </template>
            </top-alert>
        @endunless
    @endauth

    @guest
        <home-header login-url="{{ route('login') }}" register-url="{{ route('register') }}">
            <template v-slot:title>
                @if (config('portal.enable_demo_mode'))
                    <app-badge primary outline>PortalDots デモサイト</app-badge>
                @endif
                {{ config('app.name') }}
            </template>
            <template v-slot:description>{{ config('portal.description') }}</template>
            <template v-slot:admin>{{ config('portal.admin_name') }}</template>
        </home-header>
    @endguest
    @include('includes.staff_home_tab_strip')
    <app-container>
        @foreach ($pinned_pages as $pinned_page)
            <list-view>
                <template v-slot:title>{{ $pinned_page->title }}</template>
                <template v-slot:description>
                    @datetime($pinned_page->updated_at) 更新
                    @if (!$pinned_page->viewableTags->isEmpty())
                        <app-badge primary outline>限定公開</app-badge>
                    @endif
                </template>
                <list-view-card>
                    <div data-turbolinks="false" class="markdown">
                        @markdown($pinned_page->body)
                    </div>
                </list-view-card>
                @if ($pinned_page->documents->count() > 0)
                    <list-view-card>
                        <app-chips-container>
                            @foreach ($pinned_page->documents as $document)
                                <app-chip href="{{ route('documents.show', ['document' => $document]) }}" target="_blank">
                                    <template v-slot:icon>
                                        @if ($document->is_important)
                                            <i class="fas fa-exclamation-circle fa-fw text-danger"></i>
                                        @else
                                            <i class="far fa-file-alt fa-fw"></i>
                                        @endif
                                    </template>
                                    {{ $document->name }}
                                    <small class="text-muted">
                                        ({{ strtoupper($document->extension) }}
                                        •
                                        @filesize($document->size))
                                    </small>
                                </app-chip>
                            @endforeach
                        </app-chips-container>
                    </list-view-card>
                @endif
            </list-view>
        @endforeach

        @if (Gate::allows('circle.create'))
            <list-view no-card-style>
                <template v-slot:title>企画参加登録</template>
                @include('includes.participation_forms_list')
            </list-view>

            @if (count($my_circles) > 0)
                <list-view>
                    <template v-slot:title>参加登録の状況</template>
                    @each('includes.circle_list_view_item_with_status', $my_circles, 'circle')
                </list-view>
            @endif
        @endif

        @if (Auth::check() && isset($circle))
            <list-view>
                <template v-slot:title>企画情報</template>
                <list-view-card>
                    <dl>
                        @if (isset($circle->participationType))
                            <dt>参加種別</dt>
                            <dd>{{ $circle->participationType->name }}</dd>
                        @endif
                        <dt>企画名</dt>
                        <dd>{{ $circle->name }}（{{ $circle->name_yomi }}）</dd>
                        <dt>企画を出店する団体の名称</dt>
                        <dd>{{ $circle->group_name }}（{{ $circle->group_name_yomi }}）</dd>
                        @unless ($circle->places->isEmpty())
                            <dt>使用場所</dt>
                            <dd>
                                <ul>
                                    @foreach ($circle->places as $place)
                                        <li>
                                            {{ $place->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        @endunless
                    </dl>
                </list-view-card>
                <list-view-action-btn href="{{ route('circles.show', ['circle' => $circle]) }}">
                    より詳しい情報を見る
                </list-view-action-btn>
            </list-view>
        @endif

        @if ($pinned_pages->isEmpty() && $pages->isEmpty() && $documents->isEmpty() && $forms->isEmpty())
            <list-view-empty icon-class="fas fa-home" text="まだ公開コンテンツはありません"></list-view-empty>
        @endif

        @if (!$pages->isEmpty())
            <list-view>
                <template v-slot:title>お知らせ</template>
                @foreach ($pages as $page)
                    <list-view-item href="{{ route('pages.show', $page) }}"
                        {{ Auth::check() && $page->usersWhoRead->isEmpty() ? 'unread' : '' }}>
                        <template v-slot:title>
                            @if (!$page->viewableTags->isEmpty())
                                <app-badge primary outline>限定公開</app-badge>
                            @else
                                <app-badge muted outline>全員に公開</app-badge>
                            @endif
                            {{ $page->title }}
                            @if ($page->isNew())
                                <app-badge danger>NEW</app-badge>
                            @endif
                        </template>
                        <template v-slot:meta>
                            @datetime($page->updated_at)
                        </template>
                        @summary($page->body)
                    </list-view-item>
                @endforeach
                <list-view-action-btn href="{{ route('pages.index') }}">
                    他のお知らせを見る
                </list-view-action-btn>
            </list-view>
        @endif

        @if (!$documents->isEmpty())
            <list-view>
                <template v-slot:title>最近の配布資料</template>
                @foreach ($documents as $document)
                    <list-view-item href="{{ route('documents.show', ['document' => $document]) }}" newtab>
                        <template v-slot:title>
                            @if ($document->is_important)
                                <i class="fas fa-exclamation-circle fa-fw text-danger"></i>
                            @else
                                <i class="far fa-file-alt fa-fw"></i>
                            @endif
                            {{ $document->name }}
                            @if ($document->isNew())
                                <app-badge danger>NEW</app-badge>
                            @endif
                        </template>
                        <template v-slot:meta>
                            @datetime($document->updated_at) 更新
                            <br>
                            {{ strtoupper($document->extension) }}ファイル
                            •
                            @filesize($document->size)
                        </template>
                        {{ $document->description }}
                    </list-view-item>
                @endforeach
                <list-view-action-btn href="{{ route('documents.index') }}">
                    他の配布資料を見る
                </list-view-action-btn>
            </list-view>
        @endif

        @if (
            !$forms->isEmpty() &&
                Auth::check() &&
                Auth::user()->circles()->approved()->count() > 0)
            <list-view>
                <template v-slot:title>受付中の申請</template>
                @foreach ($forms as $form)
                    <list-view-item href="{{ route('forms.answers.create', ['form' => $form]) }}">
                        <template v-slot:title>
                            @if (!$form->answerableTags->isEmpty())
                                <app-badge primary outline>限定公開</app-badge>
                            @else
                                <app-badge muted outline>全員に公開</app-badge>
                            @endif
                            {{ $form->name }}
                        </template>
                        <template v-slot:meta>
                            @datetime($form->close_at) まで受付
                            @if ($form->max_answers > 1)
                                • 1企画あたり{{ $form->max_answers }}つ回答可能
                            @endif
                        </template>
                        @summary($form->description)
                    </list-view-item>
                @endforeach
                <list-view-action-btn href="{{ route('forms.index') }}">
                    他の受付中の申請を見る
                </list-view-action-btn>
            </list-view>
        @endif
    </app-container>
@endsection
