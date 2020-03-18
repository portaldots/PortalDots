@extends('v2.layouts.app')

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
                <form action="{{ route('verification.resend') }}" method="post">
                    @csrf
                    <button class="btn is-primary-inverse is-no-border is-wide">
                        <strong>確認メールを再送</strong>
                    </button>
                </form>
            </template>
        </top-alert>
    @endunless
    @if (Auth::user()->areBothEmailsVerified() && count($my_circles) < 1)
    {{-- <top-alert type="primary">
        <template v-slot:title>
            <i class="fa fa-info-circle fa-fw" aria-hidden="true"></i>
            参加登録をしましょう！
        </template>

        まだ参加登録がお済みでないようですね。まずは参加登録からはじめましょう！
        <template v-slot:cta>
            <a href="#" class="btn is-primary-inverse is-no-border is-wide">
                <strong>参加登録をはじめる</strong>
            </a>
        </template>
    </top-alert> --}}
    <top-alert type="primary" keep-visible>
        <template v-slot:title>
            <i class="fa fa-info-circle fa-fw" aria-hidden="true"></i>
            参加登録が未完了
        </template>

        団体参加登録がお済みでない場合、申請機能など、{{ config("app.name") }} の一部機能がご利用になれません<br>
        <small>(参加登録を行ってからこの表示が消えるのに時間がかかることがあります)</small>
    </top-alert>
    @endif
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
                <input id="login_id" type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" required autocomplete="username" autofocus placeholder="学籍番号・連絡先メールアドレス">
            </div>

            <div class="form-group">
                <label for="password" class="sr-only">パスワード</label>
                <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password" placeholder="パスワード">
            </div>

            <div class="form-group">
                <div class="form-checkbox">
                    <label class="form-checkbox__label">
                        <input class="form-checkbox__input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
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
    @if(empty($next_schedule) && $pages->isEmpty() && $documents->isEmpty() && $forms->isEmpty())
    <list-view-empty
        icon-class="fas fa-home"
        text="まだ公開コンテンツはありません"
    />
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
        <list-view-item
            href="{{ route('documents.show', ['document' => $document]) }}"
            newtab
        >
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
        <list-view-item
            href="{{ route('forms.answers.create', ['form' => $form]) }}"
        >
            <template v-slot:title>
                {{ $form->name }}
            </template>
            <template v-slot:meta>
                @datetime($form->close_at) まで受付
                @if ($form->max_answers > 1)
                • 1団体あたり{{ $form->max_answers }}つ回答可能
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
