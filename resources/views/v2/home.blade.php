@extends('v2.layouts.app')

@section('content')
@guest
<header class="jumbotron">
    <div class="container is-narrow">
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
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label class="form-check-label" for="remember">
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
    </div>
</header>
@endguest
@isset($next_schedule)
<div class="listview">
    <div class="listview-header">
        次の予定
    </div>
    <div class="listview-item is-fluid">
        <div class="listview-item__day_calendar">
            @include('v2.includes.day_calendar', ['date' => $next_schedule->start_at])
        </div>
        <div class="listview-item__body">
            <p class="listview-item__title">
                {{ $next_schedule->name }}
            </p>
            <p class="listview-item__summary">
                @datetime($next_schedule->start_at) • {{ $next_schedule->place }}
            </p>
            <div class="listview-item__sumarry markdown">
                @markdown($next_schedule->description)
            </div>
        </div>
    </div>
</div>
@endisset
<div class="listview">
    <div class="listview-header">
        お知らせ
    </div>
    @foreach ($pages as $page)
    <a class="listview-item" href="{{ route('pages.show', $page) }}">
        <div class="listview-item__body">
            <p class="listview-item__date">
                @datetime($page->updated_at)
            </p>
            <p class="listview-item__title">
                {{ $page->title }}
            </p>
            <p class="listview-item__summary">
                @summary($page->body)
            </p>
        </div>
    </a>
    @endforeach
</div>
<div class="listview">
    <div class="listview-header">
        最近の配布資料
    </div>
    @foreach ($documents as $document)
    <a
        href="{{ url("uploads/documents/{$document->id}") }}"
        class="listview-item"
        target="_blank"
        rel="noopener"
    >
        <div class="listview-item__body">
            <p class="listview-item__title">{{ $document->name }}</p>
            <p class="listview-item__summary">{{ $document->description }}</p>
        </div>
    </a>
    @endforeach
</div>
@endsection
