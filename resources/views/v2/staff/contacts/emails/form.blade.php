@extends('v2.layouts.no_drawer')

@section('title', 'お問い合わせ - '. isset($email) ? 'メールアドレス編集' : 'メールアドレス追加')

@section('navbar')
    <app-nav-bar-back inverse href="{{ route('staff.contacts.emails.index') }}">
        メールアドレス一覧
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        <form
            action="{{ isset($email)
                ? route('staff.contacts.emails.update', ['contact_email' => $email])
                : route('staff.contacts.emails.create') }}"
            method="post">
        @csrf
        @method(isset($email) ? 'patch' : 'post')
            <list-view>
                <template v-slot:title>
                    お問い合わせ - {{ isset($email) ? 'メールアドレス編集' : 'メールアドレス追加'}}
                </template>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        表示名
                    </template>
                    <template v-slot:description>
                        この名前がお問い合わせの宛先に表示されます
                    </template>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $email->name ?? '') }}"
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
                        @isset($email)
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
                        value="{{ old('email', $email->email ?? '') }}"
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
