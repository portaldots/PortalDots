@extends('layouts.single_column')

@section('main')
    <div class="card mb-3">
        <div class="card-header">ログアウト</div>
        <div class="card-body">
            <form class="text-center" action="{{ route('logout') }}" method="POST">
                @csrf
                <p class="lead">ログアウトしますか？</p>
                <button class="btn btn-primary" type="submit">
                    ログアウト
                </button>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    キャンセル
                </a>
            </form>
        </div>
    </div>
@endsection
