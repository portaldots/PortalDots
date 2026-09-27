@extends('layouts.no_drawer')

@inject('authSettings', 'App\Services\Auth\AuthSettings')

@section('no_footer', true)

@section('title', 'ログイン')

@push('body-class')
    has-content-fill
@endpush

@section('content')
    <div class="jumbotron">
        <app-container narrow>
            <h1 class="jumbotron__title">
                ログイン
            </h1>
            <form method="post" action="{{ route('login') }}">
                @csrf

                @if ($errors->any())
                    <div class="text-danger">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="mb-3">
                    <label for="login_id" class="visually-hidden">{{ $authSettings->loginIdLabel() }}</label>
                    <input id="login_id" type="text" class="form-control" name="login_id" value="{{ old('login_id') }}"
                        required autocomplete="username" autofocus
                        placeholder="{{ $authSettings->loginIdLabel() }}">
                </div>

                <div class="mb-3">
                    <label for="password" class="visually-hidden">パスワード</label>
                    <input id="password" type="password" class="form-control" name="password" required
                        autocomplete="current-password" placeholder="パスワード">
                </div>

                <div class="mb-3">
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

                <div class="mb-3">
                    <button type="submit" class="btn is-primary is-block">
                        <strong>ログイン</strong>
                    </button>
                </div>
                @if ($authSettings->registrationEnabled())
                    <p>
                        <a class="btn is-secondary is-block" href="{{ route('register') }}">
                            はじめての方は新規ユーザー登録
                        </a>
                    </p>
                @endif
            </form>
        </app-container>
    </div>
@endsection
