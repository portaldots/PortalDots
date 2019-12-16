@extends('layouts.app')

@section('title', (empty($circle) ? '団体情報新規作成' : '団体情報編集') . ' - ' . config('app.name') )

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

            <form method="post" action="{{ route('staff.send_emails') }}">
                @csrf
                @method('delete')
                <button type="submit" class="btn btn-danger">
                    メールの配信を全てキャンセル
                </button>
            </form>

            <p class="text-muted">間違えてメールを配信してしまった場合、メールの配信を全てキャンセル(停止)できます。</p>

            <hr>

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
        </div>
    </div>
</div>
@endsection
