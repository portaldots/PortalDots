@extends('v2.layouts.no_drawer')

@section('title', 'ログアウト')
    
@section('content')
    <div class="jumbotron">
        <app-container narrow>
            <h1 class="jumbotron__title pb-spacing-lg">
                ログアウトしますか？
            </h1>
            <form method="post" action="{{ route('logout') }}">
                @csrf
    
                <div class="form-group">
                    <button class="btn is-primary is-block" type="submit">
                        ログアウト
                    </button>
                </div>
                <div class="form-group">
                    <a href="javascript:history.back()" class="btn is-secondary is-block">
                        キャンセル
                    </a>
                </div>
            </form>
        </app-container>
    </div>
@endsection
