@extends('layouts.single_column')

@section('title', 'ログイン - ' . config('app.name'))

@section('main')
<div class="card mb-3">
    <div class="card-header">ログイン</div>
    <div class="card-body">
        <form method="POST" action="{{ route('login') }}">
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
                <input id="login_id" type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" required autofocus placeholder="学籍番号・連絡先メールアドレス">
            </div>

            <div class="form-group">
                <label for="password" class="sr-only">パスワード</label>
                <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password" placeholder="パスワード">
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label class="form-check-label" for="remember">
                        ログイン状態を維持する
                    </label>
                </div>
            </div>

            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary">
                    ログイン
                </button>

                <a class="btn btn-link" href="{{ route('password.request') }}">
                    パスワードをお忘れの場合はこちら
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">初めてですか？</div>
    <div class="card-body">
        <p>「<strong>{{ config('app.name') }}</strong>」を初めてご利用される場合はユーザー登録をお願いいたします。</p>
        <p class="mb-0">
            <a href="{{ route('register') }}" class="btn btn-primary">
                ユーザー登録
            </a>
        </p>
    </div>
</div>
@endsection
