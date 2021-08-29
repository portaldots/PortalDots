@extends('layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    @include('includes.install_header')

    <form method="post" action="{{ route('install.mail.test') }}">
        @csrf

        <app-container medium>
            <list-view>
                <template v-slot:title>テストメールの受信確認</template>
                <list-view-card>
                    {{ config('portal.contact_email') }} に対して確認メールを送信しました。<br>
                    確認メールに記載されているパスワードを入力してください。<br>
                    メールが届かない場合、
                    <a href="{{ route('install.mail.edit') }}">メール設定に誤りがないかご確認ください</a>。
                </list-view-card>
                <list-view-form-group label-for="install_password">
                    <template v-slot:label>
                        確認メールに記載のパスワード
                    </template>
                    <input id="install_password" type="text"
                        class="form-control @error('install_password') is-invalid @enderror" name="install_password"
                        required>
                    @error('install_password')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <a href="{{ route('install.mail.edit') }}" class="btn is-secondary">
                    <i class="fas fa-chevron-left"></i>
                    メール設定を変更
                </a>
                <button type="submit" class="btn is-primary is-wide">
                    <strong>次へ</strong>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </app-container>
    </form>
@endsection
