@inject('uiThemeService', 'App\Services\Utils\UIThemeService')

@extends('layouts.app')

@section('title', 'PortalDots の設定')

@section('content')
    <form method="post" action="{{ route('admin.portal.update') }}">
        @csrf

        @method('patch')

        <app-container>
            <list-view>
                <template v-slot:title>ポータル情報の設定</template>
                @foreach ($portal as $key => $value)
                    @if ($key === 'APP_FORCE_HTTPS')
                        <list-view-form-group>
                            <template v-slot:label>常時https接続</template>
                            <template v-slot:description>
                                PortalDotsでは、ポータルへのアクセスを常時https接続とすることを推奨しています。https接続を有効にするには、サーバー側での設定が必要です。詳細は、お使いのサーバーを提供している事業者へお問い合わせください。<br>
                                <strong>警告 : 設定を有効にする前に、この設定ページのURLの<code>http://</code>を<code>https://</code>に変更しても正常にアクセス可能であることを確認してください。</strong>
                            </template>

                            <div class="form-checkbox">
                                <label class="form-checkbox__label">
                                    <input id="APP_FORCE_HTTPS" type="checkbox"
                                        class="form-checkbox__input @error('APP_FORCE_HTTPS') is-invalid @enderror" name="APP_FORCE_HTTPS"
                                        value="1"
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
                                    'APP_URL' => '不必要に変更するとポータルにアクセスできなくなる可能性があります',
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
                    @endif
                @endforeach
                <list-view-form-group>
                    <template v-slot:label>
                        テーマカラー
                    </template>
                    <template v-slot:description>
                        ポータル内のボタンやリンクの色を好きな色に設定できます
                    </template>
                    <ui-primary-color-picker input-name-h="PORTAL_PRIMARY_COLOR_H" input-name-s="PORTAL_PRIMARY_COLOR_S"
                        input-name-l="PORTAL_PRIMARY_COLOR_L"
                        default-hsla-value="{{ old('theme_color', $uiThemeService->getCssPrimaryColor()) }}"></ui-primary-color-picker>
                    @error('primary_color')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">
                    保存
                </button>
            </div>

            <list-view>
                <template v-slot:title>データベースとメールの設定</template>
                <list-view-card>
                    この画面ではデータベースとメールの設定は変更できません。<br>
                    これらの設定を変更するには、サーバー上の <code>.env</code> ファイルを直接編集する必要があります。
                </list-view-card>
            </list-view>
        </app-container>
    </form>
@endsection
