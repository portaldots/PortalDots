@extends('layouts.single_column')

@section('title', 'メール認証の完了 - ' . config('app.name'))

@section('main')
    <div class="card mb-3">
        <div class="card-header">メール認証完了</div>
        <div class="card-body pt-0">
            <p class="text-center text-success display-4 mb-0 pt-4 pb-2">
                <i class="fas fa-check-circle"></i>
            </p>
            <p class="text-center text-success lead mb-0 pb-4">
                <strong>手続きが完了しました！</strong>
            </p>
            <p class="text-center mb-0">
                <a href="{{ url('/') }}" class="btn btn-primary">
                    ダッシュボードにアクセス
                </a>
            </p>
        </div>
    </div>
@endsection
