@extends('layouts.single_column')

@section('title', 'お問い合わせ - ' . config('app.name'))

@section('main')
<div class="card">
    <p class="card-header">お問い合わせ</p>
    <div class="card-body">
        <form class="form-content" method="post" action="{{ route('contacts.post') }}">
            @csrf
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex">
                    名前
                    <span class="ml-auto">{{ Auth::user()->name }}</span>
                </li>
                <li class="list-group-item d-flex">
                    学籍番号
                    <span class="ml-auto">{{ Auth::user()->student_id }}</span>
                </li>
                <li class="list-group-item d-flex">
                    メールアドレス
                    <span class="ml-auto">{{ Auth::user()->email }}</span>
                </li>

                @if (empty($circles))

                @else
                    <li class="list-group-item">
                        <label>団体名</label>
                        <select name="circle_id" class="form-control">
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
                    </li>
                @endif
                <li class="list-group-item">
                    <div class="form-group">
                        <label for="contact_body">お問い合わせ内容</label>
                        <textarea name="contact_body" id="contact_body" class="form-control {{ $errors->has('contact_body') ? 'is-invalid' : '' }}" rows="5" required>{{ old('contact_body') }}</textarea>
                        @if ($errors->has('contact_body'))
                            <div class="invalid-feedback">
                                @foreach ($errors->get('contact_body') as $message)
                                    {{ $message }}
                                @endforeach
                            </div>
                        @endif
                        <small class="form-text text-muted">確認のため、お問い合わせ内容をメールで送信いたします。</small>
                    </div>
                </li>
            </ul>
            <div class="d-flex mt-2">
                <button type="submit" class="btn btn-primary">送信</button>
                <a href="{{ route('home') }}" class="btn btn-light ml-2" role="button">キャンセル</a>
            </div>
        </form>
    </div>
</div>

@endsection
