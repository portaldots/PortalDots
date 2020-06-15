@extends('v2.layouts.no_drawer')

@section('title', (empty($contact_email) ? 'メールアドレス追加' : $contact_email->name). ' - お問い合わせ')

@section('navbar')
    <app-nav-bar-back inverse href="{{ route('staff.contacts.email.index') }}">
        メールアドレス一覧
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        <form
            action="{{ isset($contact_email)
                ? route('staff.contacts.email.update', ['contact_email' => $contact_email])
                : route('staff.contacts.email.create') }}"
            method="post">
        @csrf
        @method(isset($contact_email) ? 'patch' : 'post')
            <list-view>
                <template v-slot:title>
                    {{ isset($contact_email) ? $contact_email->name : 'メールアドレス追加'}}
                    @isset($contact_email)
                        <small><a href="{{ route('staff.contacts.email.delete', $contact_email) }}">削除</a></small>
                    @endisset
                    - お問い合わせ
                </template>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        項目名
                    </template>
                    <template v-slot:description>
                        この名前がお問い合わせの宛先に表示されます
                    </template>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $contact_email->name ?? '') }}"
                        required>
                        @error('name')
                        <template v-slot:invalid>
                            {{ $message }}
                        </template>
                        @enderror
                </list-view-form-group>
                <list-view-form-group label-for="email">
                    <template v-slot:label>
                        メールアドレス
                    </template>
                    <template v-slot:description>
                        @isset($contact_email)
                            メールアドレスを変更すると変更後のメールアドレスにメールが送信されます
                        @else
                            保存した際に設定したメールアドレスにメールが送信されます
                        @endisset
                    </template>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $contact_email->email ?? '') }}"
                        required>
                        @error('email')
                        <template v-slot:invalid>
                            {{ $message }}
                        </template>
                        @enderror
                </list-view-form-group>
            </list-view>
            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </div>
        </form>
    </app-container>
@endsection
