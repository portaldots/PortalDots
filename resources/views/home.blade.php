@extends('layouts.app')

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
        <header class="jumbotron">
            <app-container narrow>
                <h1 class="jumbotron__title">
                    {{ config('app.name') }}
                </h1>
                <p class="jumbotron__lead">
                    {{ config('portal.admin_name') }}
                </p>
                <form method="post" action="{{ route('login') }}">
                    @csrf

                    @if ($errors->any())
                        <div class="text-danger">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="login_id" class="sr-only">学籍番号または連絡先メールアドレス</label>
                        <input id="login_id" type="text" class="form-control" name="login_id" value="{{ old('login_id') }}"
                            required autocomplete="username" autofocus placeholder="学籍番号または連絡先メールアドレス">
                    </div>

                    <div class="form-group">
                        <label for="password" class="sr-only">パスワード</label>
                        <input id="password" type="password" class="form-control" name="password" required
                            autocomplete="current-password" placeholder="パスワード">
                    </div>

                    <div class="form-group">
                        <div class="form-checkbox">
                            <label class="form-checkbox__label">
                                <input class="form-checkbox__input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                ログインしたままにする
                            </label>
                        </div>
                    </div>

                    <p>
                        <a href="{{ route('password.request') }}">
                            パスワードをお忘れの場合はこちら
                        </a>
                    </p>

                    <div class="form-group">
                        <button type="submit" class="btn is-primary is-block">
                            <strong>ログイン</strong>
                        </button>
                    </div>
                    <p>
                        <a class="btn is-secondary is-block" href="{{ route('register') }}">
                            はじめての方は新規ユーザー登録
                        </a>
                    </p>
                </form>
            </app-container>
        </header>
    @endguest
    <app-container>
        @if (Auth::check() && Auth::user()->is_staff)
            <list-view>
                <template v-slot:title>
                    スタッフメニュー
                    <small class="text-muted">(スタッフにのみ表示)</small>
                </template>
                <list-view-action-btn href="{{ url('/home_staff') }}" data-turbolinks="false">
                    スタッフモードへ
                </list-view-action-btn>
            </list-view>
        @endif

        @if (Gate::allows('circle.create'))
            <list-view>
                <template v-slot:title>企画参加登録</template>
                <template v-slot:description>
                    受付期間 : @datetime($circle_custom_form->open_at)〜@datetime($circle_custom_form->close_at)
                </template>
                @if (!Auth::check())
                    <list-view-card>
                        <list-view-empty text="企画参加登録するには、まずログインしてください">
                            <p>
                                {{ config('app.name') }}の利用がはじめての場合は<a href="{{ route('register') }}">ユーザー登録</a>を行ってください。<br>
                                <a href="{{ route('login') }}">ログインはこちら</a>
                            </p>
                        </list-view-empty>
                    </list-view-card>
                @elseif (!Auth::user()->areBothEmailsVerified())
                    <list-view-card>
                        <list-view-empty icon-class="far fa-envelope" text="メール認証が未完了です">
                            <p>
                                参加登録を行うには、まずメール認証を完了させてください。
                            </p>
                            <a href="{{ route('verification.notice') }}" class="btn is-primary is-wide">
                                <strong>もっと詳しく</strong>
                            </a>
                        </list-view-empty>
                    </list-view-card>
                @elseif (count($my_circles) === 0)
                    <list-view-card>
                        <list-view-empty icon-class="far fa-star" text="参加登録をしましょう！">
                            <p>
                                まだ参加登録がお済みでないようですね。<br>
                                まずは参加登録からはじめましょう！
                            </p>
                            <a href="{{ route('circles.create') }}" class="btn is-primary is-wide">
                                <strong>参加登録をはじめる</strong>
                            </a>
                        </list-view-empty>
                    </list-view-card>
                @else
                    @each('includes.circle_list_view_item_with_status', $my_circles, 'circle')
                    <list-view-action-btn href="{{ route('circles.create') }}" icon-class="fas fa-plus">
                        別の企画を参加登録する
                    </list-view-action-btn>
                @endif
            </list-view>
        @endif

        @if(empty($next_schedule) && $pages->isEmpty() && $documents->isEmpty() && $forms->isEmpty())
            <list-view-empty icon-class="fas fa-home" text="まだ公開コンテンツはありません" />
        @endif

        @isset($next_schedule)
            <list-view>
                <template v-slot:title>次の予定</template>
                <list-view-item>
                    <template v-slot:title>
                        {{ $next_schedule->name }}
                    </template>
                    <template v-slot:meta>
                        @datetime($next_schedule->start_at)〜 • {{ $next_schedule->place }}
                    </template>
                    @isset ($next_schedule->description)
                        <div data-turbolinks="false" class="markdown">
                            <hr>
                            @markdown($next_schedule->description)
                        </div>
                    @endisset
                </list-view-item>
                <list-view-action-btn href="{{ route('schedules.index') }}">
                    他の予定を見る
                </list-view-action-btn>
            </list-view>
        @endisset

        @if (!$pages->isEmpty())
            <list-view>
                <template v-slot:title>お知らせ</template>
                @foreach ($pages as $page)
                    <list-view-item
                        href="{{ route('pages.show', $page) }}"
                        {{ Auth::check() && $page->usersWhoRead->isEmpty() ? 'unread' : '' }}
                    >
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
                @if ($remaining_pages_count > 0)
                    <list-view-action-btn href="{{ route('pages.index') }} ">
                        残り {{ $remaining_pages_count }} 件のお知らせを見る
                    </list-view-action-btn>
                @endif
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
                            @isset($document->schedule)
                                •
                                {{ $document->schedule->name }}で配布
                            @endisset
                            <br>
                            {{ strtoupper($document->extension) }}ファイル
                            •
                            @filesize($document->size)
                        </template>
                        {{ $document->description }}
                    </list-view-item>
                @endforeach
                @if ($remaining_documents_count > 0)
                    <list-view-action-btn href="{{ route('documents.index') }} ">
                        残り {{ $remaining_documents_count }} 件の配布資料を見る
                    </list-view-action-btn>
                @endif
            </list-view>
        @endif

        @if (!$forms->isEmpty() && Auth::check() && Auth::user()->circles()->approved()->count() > 0)
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
                @if ($remaining_forms_count > 0)
                    <list-view-action-btn href="{{ route('forms.index') }} ">
                        残り {{ $remaining_forms_count }} 件の受付中の申請を見る
                    </list-view-action-btn>
                @endif
            </list-view>
        @endif
    </app-container>
@endsection
