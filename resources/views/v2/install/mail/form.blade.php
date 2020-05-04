@extends('v2.layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@section('content')
    @include('v2.includes.install_header')

    <form method="post" action="{{ route('install.mail.update') }}">
        @csrf

        @method('patch')

        <app-container medium>
            <list-view>
                <template v-slot:title>メール配信の設定</template>
                <template v-slot:description>PortalDots のシステムからユーザーや実行委員会へメールを配信するための設定です。</template>
                @foreach ($mail as $key => $value)
                    <list-view-form-group label-for="name">
                        <template v-slot:label>
                            {{
                                [
                                    'MAIL_HOST' => 'メールサーバーのホスト',
                                    'MAIL_PORT' => 'メールサーバーのポート',
                                    'MAIL_USERNAME' => 'メールユーザー名',
                                    'MAIL_PASSWORD' => 'メールパスワード',
                                    'MAIL_FROM_ADDRESS' => 'PortalDots から配信されるメールの差出人メールアドレス',
                                    'MAIL_FROM_NAME' => 'PortalDots から配信されるメールの差出人の名前',
                                ][$key]
                            }}
                        </template>
                        <input id="{{ $key }}" type="text" class="form-control @error($key) is-invalid @enderror" name="{{ $key }}"
                            value="{{ old($key, $key === 'MAIL_FROM_NAME' && empty($value) ? config('app.name') : $value ) }}" required>
                        @error($key)
                        <template v-slot:invalid>{{ $message }}</template>
                        @enderror
                    </list-view-form-group>
                @endforeach
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <a href="{{ route('install.database.edit') }}" class="btn is-secondary">
                    <i class="fas fa-chevron-left"></i>
                    戻る
                </a>
                <button type="submit" class="btn is-primary is-wide">
                    <strong>テストメールを送信</strong>
                    <i class="fas fa-chevron-right"></i>
                </button>
                <p class="pt-spacing-md">
                    「テストメールを送信」をクリックすると、PortalDots から {{ config('portal.contact_email') }} へテストメールが送信されます。<br>
                    <strong>PortalDots のインストールを完了するには、テストメールに記載されているパスワードが必要になります。</strong>
                </p>
            </div>
        </app-container>
    </form>
@endsection
