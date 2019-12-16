@extends('layouts.single_column')

@section('title', (isset($user) ? 'ユーザー情報の編集 - ' : 'ユーザー登録 - ' ) . config('app.name'))

@section('main')
<div class="card">
    <div class="card-header">{{ isset($user) ? 'ユーザー情報の編集' : 'ユーザー登録' }}</div>

    <div class="card-body">
        @empty($user)
            「<strong>{{ config('app.name') }}</strong>」にユーザー登録します。
            <hr>
        @endempty

        <form method="POST" action="{{ isset($user) ? route('user.update') : route('register') }}">
            @method(isset($user) ? 'patch' : 'post')
            @csrf

            <div class="form-group row">
                <label for="student_id" class="col-md-4 col-form-label text-md-right">学籍番号</label>

                <div class="col-md-8">
                    <input id="student_id" type="text" class="form-control @error('student_id') is-invalid @enderror" name="student_id" value="{{ old('student_id', isset($user) ? $user->student_id : '' ) }}" {{ !empty($circles) ? 'disabled' : '' }} required>
                    @if (!empty($circles))
                        <small class="form-text text-muted">団体に所属しているため修正できません</small>
                    @endif
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
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', isset($user) ? $user->name : '' ) }}" {{ !empty($circles) ? 'disabled' : '' }} required autocomplete="name">
                    <small class="form-text text-muted">{{ !empty($circles) ? '団体に所属しているため修正できません' : '姓と名の間にはスペースを入れてください' }}</small>

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
                    <input id="name_yomi" type="text" class="form-control @error('name_yomi') is-invalid @enderror" name="name_yomi" value="{{ old('name_yomi', isset($user) ? $user->name_yomi : '' ) }}" {{ !empty($circles) ? 'disabled' : '' }} required>
                    <small class="form-text text-muted">{{ !empty($circles) ? '団体に所属しているため修正できません' : '姓と名の間にはスペースを入れてください' }}</small>

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
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', isset($user) ? $user->email : '' ) }}" required autocomplete="email">
                    <small class="form-text text-muted">連絡先メールアドレスとして学校発行のメールアドレスもご利用になれます</small>
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
                    <input id="tel" type="text" class="form-control @error('tel') is-invalid @enderror" name="tel" value="{{ old('tel', isset($user) ? $user->tel : '' ) }}" required>

                    @error('tel')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            @isset($user)
                <hr>
                <p>保存するには現在のパスワードを入力してください</p>
            @endisset
            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right">
                    @isset($user)
                        現在のパスワード
                    @else
                        パスワード
                    @endisset
                </label>

                <div class="col-md-8">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            @empty($user)
                <div class="form-group row">
                    <label for="password-confirm" class="col-md-4 col-form-label text-md-right">パスワード(確認)</label>

                    <div class="col-md-8">
                        <input id="password-confirm" type="password" class="form-control @error('password') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">
                    </div>
                </div>
            @endempty

            <div class="form-group row mb-0">
                <div class="col-md-8 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        {{ isset($user) ? '保存' : '登録' }}
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-default" role="button">キャンセル</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
