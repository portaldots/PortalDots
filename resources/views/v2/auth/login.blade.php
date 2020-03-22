@extends('v2.layouts.no_drawer')

@section('title', 'ログイン')
    
@section('content')
    <div class="jumbotron">
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
    </div>
@endsection
