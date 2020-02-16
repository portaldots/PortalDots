@extends('v2.layouts.app')

@section('title', __('お問い合わせ'))

{{-- TODO: 完全にLaravel化したら、以下のdrawerセクションは完全削除する --}}
@section('drawer')
<a class="drawer-header" href="{{ url('/') }}" data-turbolinks="false">
    {{ config('app.name') }}
</a>
<nav class="drawer-nav">
    <ul class="drawer-nav__list">
        <li class="drawer-nav__item">
            <a href="{{ url('/') }}" class="drawer-nav__link" data-turbolinks="false">
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
<form method="post" action="{{ route('contacts.post') }}">
    @csrf

    <app-container>
        <list-view header-title="{{ __('お問い合わせ') }}">
            <list-view-form-group label-for="recipient">
                <template v-slot:label>{{ __('宛先') }}</template>
                <input type="text" id="recipient" readonly value="{{ config('portal.admin_name') }}" class="form-control is-plaintext">
            </list-view-form-group>
            <list-view-form-group label-for="name">
                <template v-slot:label>{{ __('差出人') }}</template>
                <input type="text" id="name" readonly value="{{ Auth::user()->name }}" class="form-control is-plaintext">
            </list-view-form-group>
            <list-view-form-group label-for="student_id">
                <template v-slot:label>{{ __('学籍番号') }}</template>
                <input type="text" id="student_id" readonly value="{{ Auth::user()->student_id }}" class="form-control is-plaintext">
            </list-view-form-group>
            <list-view-form-group label-for="email">
                <template v-slot:label>{{ __('メールアドレス') }}</template>
                <template v-slot:description>
                    {!! __('メールアドレスの変更は :link で行えます。', ['link' => '<a href="' . route('user.edit') . '">' . __('ユーザー設定') . '</a>']) !!}
                </template>
                <input type="email" id="email" readonly value="{{ Auth::user()->email }}" class="form-control is-plaintext">
            </list-view-form-group>
            @unless (empty($circles) || count($circles) < 1)
                <list-view-form-group label-for="circle_id">
                    <template v-slot:label>{{ __('団体名') }}</template>
                    <template v-slot:description>{{ __('どの団体としてお問い合わせするのか選択してください') }}</template>
                    <select name="circle_id" id="circle_id" class="form-control">
                        @foreach ($circles as $circle)
                            @if (!empty(old('circle_id')) && old('circle_id') === $circle->id)
                                <option value="{{ $circle->id }}" selected>{{ $circle->name }}</option>
                            @else
                                <option value="{{ $circle->id }}">{{ $circle->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </list-view-form-group>
            @endunless
            <list-view-form-group label-for="contact_body">
                <template v-slot:label>{{ __('お問い合わせ内容') }}</template>
                <template v-slot:description>{{ __('確認のため、お問い合わせ内容をメールで送信いたします') }}</template>
                <textarea name="contact_body" id="contact_body" class="form-control {{ $errors->has('contact_body') ? 'is-invalid' : '' }}" rows="10" required>{{ old('contact_body') }}</textarea>

                @if ($errors->has('contact_body'))
                    <template v-slot:invalid>
                    @foreach ($errors->get('contact_body') as $message)
                        {{ $message }}<br>
                    @endforeach
                    </template>
                @endif
            </list-view-form-group>
        </list-view>
    </app-container>

    <app-container class="text-center pt-spacing-md">
        <button type="submit" class="btn is-primary is-wide">
            {{ __('送信') }}
        </button>
    </app-container>
</form>
@endsection
