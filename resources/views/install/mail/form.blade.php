@extends('layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    @include('includes.install_header')

    <install-mail-settings-form csrf-token="{{ csrf_token() }}" update-settings-path="{{ route('install.mail.update') }}"
        send-test-path="{{ route('install.mail.send_test') }}" next-screen-path="{{ route('install.admin.create') }}">
        <app-container medium>
            <list-view>
                <template v-slot:title>メール配信の設定</template>
                <template v-slot:description>PortalDots のシステムからユーザーや実行委員会へメールを配信するための設定です。</template>
                @foreach ($mail as $key => $value)
                    <list-view-form-group label-for="name">
                        <template v-slot:label>
                            {{ $labels[$key] }}
                        </template>
                        <input id="{{ $key }}" type="text" class="form-control @error($key) is-invalid @enderror"
                            name="{{ $key }}"
                            value="{{ old($key, $key === 'MAIL_FROM_NAME' && empty($value) ? config('app.name') : $value) }}"
                            required>
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
                    保存して次へ
                    <i class="fas fa-chevron-right"></i>
                </button>
                <p class="pt-spacing-md">
                    「保存して次へ」をクリックすると、PortalDots から {{ config('portal.contact_email') }} へテストメールが送信されます。<br>
                    正常にメールを受け取ることができるかどうか、ご確認ください。
                </p>
            </div>
        </app-container>
    </install-mail-settings-form>
@endsection
