@extends('v2.layouts.app')

@section('content')

    @auth
        @unless (Auth::user()->areBothEmailsVerified())
            <top-alert type="primary" keep-visible>
                <template v-slot:title>
                    <i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i>
                    {{ Auth::user()->is_verified_by_staff ? '連絡先メールアドレスを変更するか、' : '' }}
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
                    <form action="{{ route('verification.resend') }}" method="post">
                        @csrf
                        <button class="btn is-primary-inverse is-no-border is-wide">
                            <strong>確認メールを再送</strong>
                        </button>
                    </form>
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
                        <label for="login_id" class="sr-only">学籍番号・連絡先メールアドレス</label>
                        <input id="login_id" type="text" class="form-control" name="login_id" value="{{ old('login_id') }}"
                            required autocomplete="username" autofocus placeholder="学籍番号・連絡先メールアドレス">
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
        @if (Auth::check() && Auth::user()->areBothEmailsVerified() && Auth::user()->can('circle.create'))
            <list-view>
                <template v-slot:title>企画参加登録</template>
                <template v-slot:description>
                    受付期間 : @datetime($circle_custom_form->open_at)〜@datetime($circle_custom_form->close_at)
                </template>
                @if (count($my_circles) === 0)
                    <list-view-card>
                        <list-view-empty icon-class="far fa-star" text="参加登録をしましょう！">
                            <p>
                                まだ参加登録がお済みでないようですね。<br>
                                まずは参加登録からはじめましょう！
                            </p>
                            <a href="{{ route('circles.create') }}" class="btn is-primary is-wide">
                                参加登録をはじめる
                            </a>
                        </list-view-empty>
                    </list-view-card>
                @else
                    @foreach ($my_circles as $circle)
                        @if (!$circle->hasSubmitted() && $circle->canSubmit())
                            <list-view-item href="{{ route('circles.confirm', ['circle' => $circle]) }}">
                                <template v-slot:title>
                                    <span class="text-primary">
                                        📮
                                        ここをクリックして「{{ $circle->name }}」の参加登録を提出しましょう！
                                    </span>
                                </template>
                                <template v-slot:meta>
                                    学園祭係(副責任者)の招待が完了しました。ここをクリックして登録内容に不備がないかどうかを確認し、参加登録を提出しましょう。
                                </template>
                            </list-view-item>
                        @elseif ($circle->isPending())
                            <list-view-item>
                                <template v-slot:title>
                                    💭
                                    「{{ $circle->name }}」の参加登録の内容を確認中です
                                </template>
                                <template v-slot:meta>
                                    ただいま参加登録の内容を確認しています。{{ config('portal.admin_name') }}より指示がある場合は従ってください。また、内容確認のためご連絡を差し上げる場合がございます。
                                </template>
                            </list-view-item>
                        @elseif (!$circle->hasSubmitted() && !$circle->canSubmit())
                            <list-view-item href="{{ route('circles.users.index', ['circle' => $circle]) }}">
                                <template v-slot:title>
                                    <span class="text-primary">
                                        📩
                                        ここをクリックして「{{ $circle->name }}」の学園祭係(副責任者)を招待しましょう！
                                    </span>
                                </template>
                                <template v-slot:meta>
                                    参加登録を提出するには、ここをクリックして学園祭係(副責任者)を招待しましょう。
                                </template>
                            </list-view-item>
                        @elseif ($circle->hasApproved())
                            <list-view-item>
                                <template v-slot:title>
                                    🎉
                                    「{{ $circle->name }}」の参加登録は受理されました
                                </template>
                            </list-view-item>
                        @elseif ($circle->hasRejected())
                            <list-view-item @isset ($circle->status_reason)
                                    href="{{ route('circles.status', ['circle' => $circle]) }}"
                                @endisset
                                >
                                <template v-slot:title>
                                    <span class="text-danger">
                                        ⚠️
                                        「{{ $circle->name }}」の参加登録は受理されませんでした
                                    </span>
                                </template>
                                @isset ($circle->status_reason)
                                    <template v-slot:meta>
                                        詳細はこちら
                                    </template>
                                @endisset
                            </list-view-item>
                        @endif
                    @endforeach
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
                        <div class="markdown">
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
                    <list-view-item href="{{ route('pages.show', $page) }}">
                        <template v-slot:title>
                            {{ $page->title }}
                            @if ($page->isNew())
                                <span class="badge is-danger">NEW</span>
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
                                <span class="badge is-danger">NEW</span>
                            @endif
                        </template>
                        <template v-slot:meta>
                            @datetime($document->updated_at) 更新
                            @isset($document->schedule)
                                •
                                {{ $document->schedule->name }}で配布
                            @endisset
                        </template>
                        @summary($document->description)
                    </list-view-item>
                @endforeach
                @if ($remaining_documents_count > 0)
                    <list-view-action-btn href="{{ route('documents.index') }} ">
                        残り {{ $remaining_documents_count }} 件の配布資料を見る
                    </list-view-action-btn>
                @endif
            </list-view>
        @endif

        @if (!$forms->isEmpty())
            <list-view>
                <template v-slot:title>受付中の申請</template>
                @foreach ($forms as $form)
                    <list-view-item href="{{ route('forms.answers.create', ['form' => $form]) }}">
                        <template v-slot:title>
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
