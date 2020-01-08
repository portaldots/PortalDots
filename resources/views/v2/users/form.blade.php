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
            ユーザー設定
        </h1>
    </div>
</header>
<div class="container">
    <form method="POST" action="{{ route('user.update') }}">
        @method('patch')
        @csrf

        <div class="form-group">
            <label for="student_id" class="form-label">学籍番号</label>
            <input id="student_id" type="text" class="form-control @error('student_id') is-invalid @enderror" name="student_id" value="{{ old('student_id', isset($user) ? $user->student_id : '' ) }}" {{ !empty($circles) ? 'disabled' : '' }} required autocomplete="username">
            @if (!empty($circles))
                <small class="form-text text-muted">団体に所属しているため修正できません</small>
            @endif
            @error('student_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="name" class="form-label">名前</label>
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', isset($user) ? $user->name : '' ) }}" {{ !empty($circles) ? 'disabled' : '' }} required autocomplete="name">
            <small class="form-text text-muted">{{ !empty($circles) ? '団体に所属しているため修正できません' : '姓と名の間にはスペースを入れてください' }}</small>

            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="name_yomi" class="form-label">名前(よみ)</label>
            <input id="name_yomi" type="text" class="form-control @error('name_yomi') is-invalid @enderror" name="name_yomi" value="{{ old('name_yomi', isset($user) ? $user->name_yomi : '' ) }}" {{ !empty($circles) ? 'disabled' : '' }} required>
            <small class="form-text text-muted">{{ !empty($circles) ? '団体に所属しているため修正できません' : '姓と名の間にはスペースを入れてください' }}</small>

            @error('name_yomi')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">連絡先メールアドレス</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', isset($user) ? $user->email : '' ) }}" required autocomplete="email">
            <small class="form-text text-muted">連絡先メールアドレスとして学校発行のメールアドレスもご利用になれます</small>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="tel" class="form-label">連絡先電話番号</label>
            <input id="tel" type="tel" class="form-control @error('tel') is-invalid @enderror" name="tel" value="{{ old('tel', isset($user) ? $user->tel : '' ) }}" required>
            @error('tel')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <hr>
        <div class="form-group">
            <label for="password" class="form-label">保存するには現在のパスワードを入力してください</label>
            <input
                id="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                required
                autocomplete="current-password"
            >
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group pt-spacing-md">
            <button type="submit" class="btn is-primary is-block">保存</button>
        </div>

    </form>
</div>
@endsection
