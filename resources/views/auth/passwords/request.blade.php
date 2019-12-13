@extends('layouts.single_column')

@section('title', 'パスワードの再設定 - ' . config('app.name'))

@section('main')
<div class="card mb-3">
    <div class="card-header">パスワードを再設定</div>
    <div class="card-body">
        <form method="POST" action="{{ route('password.request') }}">
            @csrf

            @if ($errors->any())
                <div class="text-danger">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <p>まず、「{{ config('app.name') }}」にログインするために使用していた学籍番号または連絡先メールアドレスを入力してください。</p>
            <p>連絡先メールアドレスに対し、パスワード再設定に関するご案内を差し上げます。</p>

            <hr>

            <div class="form-group">
                <label for="login_id">学籍番号または連絡先メールアドレス</label>
                <input id="login_id" type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" required autofocus>
            </div>

            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary">
                    次へ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
