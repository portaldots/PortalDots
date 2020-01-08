@extends('v2.layouts.app')

@section('title', 'お問い合わせ')

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
<header class="header">
    <div class="container">
        <h1 class="header__title">
            お問い合わせ
        </h1>
    </div>
</header>
<main class="container">
    <form class="form" method="post" action="{{ route('contacts.post') }}">
        @csrf
        <div class="form-group">
            <label class="form-label" for="recipient">宛先</label>
            <input type="text" id="recipient" readonly value="{{ config('portal.admin_name') }}" class="form-control is-plaintext">
        </div>
        <div class="form-group">
            <label class="form-label" for="name">名前</label>
            <input type="text" id="name" readonly value="{{ Auth::user()->name }}" class="form-control is-plaintext">
        </div>
        <div class="form-group">
            <label class="form-label" for="student_id">学籍番号</label>
            <input type="text" id="student_id" readonly value="{{ Auth::user()->student_id }}" class="form-control is-plaintext">
        </div>
        <div class="form-group">
            <label class="form-label" for="email">メールアドレス</label>
            <input type="email" id="email" readonly value="{{ Auth::user()->email }}" class="form-control is-plaintext">
        </div>
        @unless (empty($circles) || count($circles) < 1)
            <div class="form-group">
                <label class="form-label" for="circle_id">団体名</label>
                <select name="circle_id" id="circle_id" class="form-control">
                    @foreach ($circles as $circle)
                        @if (!empty(old('circle_id')) && old('circle_id') === $circle->id)
                            <option value="{{ $circle->id }}" selected>{{ $circle->name }}</option>
                        @else
                            <option value="{{ $circle->id }}">{{ $circle->name }}</option>
                        @endif
                    @endforeach
                </select>
                @if (count($circles) > 1)
                    <small class="form-text text-muted">どの団体としてお問い合わせするのか選択してください。</small>
                @endif
            </div>
        @endunless
        <div class="form-group">
            <label class="form-label" for="contact_body">お問い合わせ内容</label>
            <textarea name="contact_body" id="contact_body" class="form-control {{ $errors->has('contact_body') ? 'is-invalid' : '' }}" rows="10" required>{{ old('contact_body') }}</textarea>
            @if ($errors->has('contact_body'))
                <div class="invalid-feedback">
                    @foreach ($errors->get('contact_body') as $message)
                        {{ $message }}
                    @endforeach
                </div>
            @endif
            <small class="form-text text-muted">確認のため、お問い合わせ内容をメールで送信いたします。</small>
        </div>
        <button type="submit" class="btn is-primary">送信</button>
    </form>
</main>
@endsection
