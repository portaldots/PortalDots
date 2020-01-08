@extends('v2.layouts.app')

@section('title', 'ユーザー設定')

{{-- TODO: 完全にLaravel化したら、以下のdrawerセクションは完全削除する --}}
@section('drawer')
<a class="drawer-header" href="{{ url('/') }}">
    {{ config('app.name') }}
</a>
<nav class="drawer-nav">
    <ul class="drawer-nav__list">
        <li class="drawer-nav__item">
            <a href="{{ url('/') }}" class="drawer-nav__link">
                ホームに戻る
            </a>
        </li>
    </ul>
</nav>
@endsection

@section('bottom_tabs')
{{-- TODO: 完全にLaravel化したら、このセクションは完全削除する --}}
@endsection

@section('content')
@include('v2.includes.user_settings_tab_strip')
<header class="header">
    <div class="container">
        <h1 class="header__title">
            パスワード変更
        </h1>
    </div>
</header>
<div class="container">
    <form method="POST" action="{{ route('change_password') }}">
        @csrf

        <div class="form-group">
            <label for="password" class="form-label">現在のパスワード</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
            @error('password')
            <span class="form-invalid-feedback" role="alert">
                {{ $message }}
            </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="new_password" class="form-label">新しいパスワード</label>
            <input id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" required autocomplete="new-password">
            @error('new_password')
            <span class="form-invalid-feedback" role="alert">
                {{ $message }}
            </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="new_password" class="form-label">新しいパスワード(確認)</label>
            <input id="new_password_confirmation" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password_confirmation" required autocomplete="new-password">
        </div>

        <div class="form-group pt-spacing-md">
            <button type="submit" class="btn is-primary is-block">保存</button>
        </div>
    </form>
</div>
@endsection
