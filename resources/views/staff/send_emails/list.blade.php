@extends('layouts.app')

@section('title', 'メールの一斉送信 - ' . config('app.name') )

@section('content')
<div class="container">
    @if (session('toast'))
        <div class="alert alert-success" role="alert">
            {{ session('toast') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            配信するお知らせを選択
        </div>
        <div class="card-body">

            <form method="post" action="{{ route('staff.send_emails') }}" class="mb-1">
                @csrf
                @method('delete')
                <button type="submit" class="btn btn-danger">
                    メールの配信を全てキャンセル
                </button>
            </form>

            <p class="text-muted">間違えてメールを配信してしまった場合、メールの配信を全てキャンセル(停止)できます。</p>

            <hr>

            @if (count($pages) === 0)
                <p class="text-muted lead text-center mb-1">お知らせがありません</p>
                <p class="text-muted text-center">メールを一斉送信するには、まずお知らせを作成してください</p>
            @endif

            @foreach ($pages as $page)
                <details>
                    <summary>
                        {{ $page->title }}
                    </summary>
                    <pre class="card my-3 p-3">{{ $page->body }}</pre>
                    <div class="mb-3">
                        <form
                            method="post"
                            action="{{ route('staff.send_emails') }}"
                            class="d-inline"
                            onsubmit="return confirm('全ユーザーにメールが配信されますが、よろしいですか？')"
                        >
                            @csrf
                            <input type="hidden" name="page_id" value="{{ $page->id }}">
                            <button type="submit" class="btn btn-primary">
                                全ユーザーに一斉送信する
                            </button>
                        </form>
                    </div>
                </details>
                <hr>
            @endforeach

            <p class="text-center">
                <a href="{{ url('home_staff/pages') }}" class="btn btn-primary">
                    お知らせ管理
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
