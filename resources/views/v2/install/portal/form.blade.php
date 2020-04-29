@extends('v2.layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@section('content')
    @include('v2.includes.install_header')

    <form method="post" action="{{ route('install.portal.update') }}">
        @csrf

        @method('patch')

        <app-container medium>
            <list-view>
                <template v-slot:title>ポータルの情報</template>
                <template v-slot:description>ポータルや実行委員会について教えてください</template>
                @foreach ($portal as $key => $value)
                    <list-view-form-group label-for="name">
                        <template v-slot:label>
                            {{ [
                                        'APP_NAME' => 'ポータルの名前',
                                        'APP_URL' => 'ポータルのURL',
                                        'PORTAL_ADMIN_NAME' => '実行委員会の名称',
                                        'PORTAL_CONTACT_EMAIL' => '実行委員会のメールアドレス',
                                        'PORTAL_UNIVEMAIL_DOMAIN' => '学校発行メールアドレスのドメイン'
                                    ][$key] }}
                        </template>
                        <template v-slot:description>
                            {{ [
                                        'APP_NAME' => '例 : 野田祭ウェブシステム',
                                        'APP_URL' => 'このページの URL から /install/portal を抜いたもの',
                                        'PORTAL_ADMIN_NAME' => '',
                                        'PORTAL_CONTACT_EMAIL' => 'ユーザーからの問い合わせはこのメールアドレスに届きます',
                                        'PORTAL_UNIVEMAIL_DOMAIN' => '例 : ed.tus.ac.jp ・ ユーザーがポータルにユーザー登録するには、アットマーク(@)以降がこの文字列となっているメールアドレスをユーザーが所有している必要があります'
                                    ][$key] }}
                        </template>
                        <input id="{{ $key }}" type="text" class="form-control @error($key) is-invalid @enderror"
                            name="{{ $key }}"
                            value="{{ old($key, $key === 'APP_URL' && empty($value) ? $suggested_app_url : $value) }}" required>
                        @error($key)
                        <template v-slot:invalid>{{ $message }}</template>
                        @enderror
                    </list-view-form-group>
                @endforeach
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">
                    保存して次へ
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </app-container>
    </form>
@endsection
