@inject('uiThemeService', 'App\Services\Utils\UIThemeService')

@extends('layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    @include('includes.install_header')

    <form method="post" action="{{ route('install.portal.update') }}">
        @csrf

        @method('patch')

        <app-container medium>
            <list-view>
                <template v-slot:title>ポータルの情報</template>
                <template v-slot:description>ポータルや実行委員会について教えてください。</template>
                @foreach ($portal as $key => $value)
                    @if ($key === 'APP_FORCE_HTTPS')
                        <list-view-form-group>
                            <template v-slot:label>常時https接続</template>
                            <template v-slot:description>
                                PortalDotsでは、ポータルへのアクセスを常時https接続とすることを推奨しています。https接続を有効にするには、サーバー側での設定が必要です。詳細は、お使いのサーバーを提供している事業者へお問い合わせください。<br>
                                <strong>警告 :
                                    設定を有効にする前に、この設定ページのURLの<code>http://</code>を<code>https://</code>に変更しても正常にアクセス可能であることを確認してください。</strong>
                            </template>

                            <div class="form-checkbox">
                                <label class="form-checkbox__label">
                                    <input id="APP_FORCE_HTTPS" type="checkbox"
                                        class="form-checkbox__input @error('APP_FORCE_HTTPS') is-invalid @enderror"
                                        name="APP_FORCE_HTTPS" value="1"
                                        {{ old($key, $value) === 'true' ? 'checked' : '' }}>
                                    https接続を強制する
                                </label>
                            </div>

                            @error('APP_FORCE_HTTPS')
                                <template v-slot:invalid>{{ $message }}</template>
                            @enderror
                        </list-view-form-group>
                    @elseif (strpos($key, 'PORTAL_PRIMARY_COLOR_') === 0)
                        @continue
                    @else
                        <list-view-form-group label-for="name">
                            <template v-slot:label>
                                {{ $labels[$key] }}
                            </template>
                            <template v-slot:description>
                                {{ [
                                    'APP_NAME' => '例 : 野田祭ウェブシステム',
                                    'APP_URL' => 'このページの URL から /install/portal を抜いたもの',
                                    'PORTAL_ADMIN_NAME' => '',
                                    'PORTAL_CONTACT_EMAIL' => 'ユーザーからの問い合わせはこのメールアドレスに届きます',
                                    'PORTAL_UNIVEMAIL_DOMAIN' => '例 : ed.tus.ac.jp ・ ユーザーがポータルにユーザー登録するには、アットマーク(@)以降がこの文字列となっているメールアドレスをユーザーが所有している必要があります',
                                ][$key] }}
                            </template>
                            <input id="{{ $key }}" type="text"
                                class="form-control @error($key) is-invalid @enderror" name="{{ $key }}"
                                value="{{ old($key, $key === 'APP_URL' && empty($value) ? $suggested_app_url : $value) }}"
                                required>
                            @error($key)
                                <template v-slot:invalid>{{ $message }}</template>
                            @enderror
                        </list-view-form-group>
                    @endif
                @endforeach
                <list-view-form-group>
                    <template v-slot:label>
                        アクセントカラー
                    </template>
                    <template v-slot:description>
                        ポータル内のボタンやリンクの色を好きな色に設定できます。ダークテーマの場合、ここで設定した色よりも明るい色になります。
                    </template>
                    <ui-primary-color-picker input-name-h="PORTAL_PRIMARY_COLOR_H" input-name-s="PORTAL_PRIMARY_COLOR_S"
                        input-name-l="PORTAL_PRIMARY_COLOR_L"
                        default-hsla-value="{{ $uiThemeService->getCssPrimaryColor() }}"></ui-primary-color-picker>
                    @error('primary_color')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
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
