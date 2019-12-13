@extends('layouts.single_column')

@section('title', 'ユーザー登録 - ' . config('app.name'))

@section('main')
<div class="card">
    <div class="card-header">ユーザー登録</div>

    <div class="card-body">
        「<strong>{{ config('app.name') }}</strong>」にユーザー登録します。

        <hr>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group row">
                <label for="student_id" class="col-md-4 col-form-label text-md-right">学籍番号</label>

                <div class="col-md-8">
                    <input id="student_id" type="text" class="form-control @error('student_id') is-invalid @enderror" name="student_id" value="{{ old('student_id') }}" required>

                    @error('student_id')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">名前</label>

                <div class="col-md-8">
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name">
                    <small class="form-text text-muted">姓と名の間にはスペースを入れてください</small>

                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="name_yomi" class="col-md-4 col-form-label text-md-right">名前(よみ)</label>

                <div class="col-md-8">
                    <input id="name_yomi" type="text" class="form-control @error('name_yomi') is-invalid @enderror" name="name_yomi" value="{{ old('name_yomi') }}" required>
                    <small class="form-text text-muted">姓と名の間にはスペースを入れてください</small>

                    @error('name_yomi')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="email" class="col-md-4 col-form-label text-md-right">連絡先メールアドレス</label>

                <div class="col-md-8">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="tel" class="col-md-4 col-form-label text-md-right">連絡先電話番号</label>

                <div class="col-md-8">
                    <input id="tel" type="text" class="form-control @error('tel') is-invalid @enderror" name="tel" value="{{ old('tel') }}" required>

                    @error('tel')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right">パスワード</label>

                <div class="col-md-8">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="password-confirm" class="col-md-4 col-form-label text-md-right">パスワード(確認)</label>

                <div class="col-md-8">
                    <input id="password-confirm" type="password" class="form-control @error('password') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        登録する
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
