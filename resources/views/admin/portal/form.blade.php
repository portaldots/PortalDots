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
                                {{ [
                                    'APP_NAME' => 'ポータルの名前',
                                    'APP_URL' => 'ポータルのURL',
                                    'PORTAL_ADMIN_NAME' => '実行委員会の名称',
                                    'PORTAL_DESCRIPTION' => 'ポータルの説明',
                                    'PORTAL_CONTACT_EMAIL' => '実行委員会のメールアドレス',
                                    'PORTAL_STUDENT_ID_NAME' => '個人ごとに割り振られる番号(学籍番号)の呼び方',
                                    'PORTAL_UNIVEMAIL_NAME' => '学校発行メールアドレスの呼び方',
                                    'PORTAL_UNIVEMAIL_LOCAL_PART' => '学校発行メールアドレスのローカルパート種別',
                                    'PORTAL_UNIVEMAIL_DOMAIN_PART' => '学校発行メールアドレスのドメイン',
                                ][$key] }}
                            </template>
                            <template v-slot:description>
                                {{ [
                                    'APP_NAME' => '例 : 野田祭ウェブシステム',
                                    'APP_URL' => '不必要に変更するとポータルにアクセスできなくなる可能性があります',
                                    'PORTAL_ADMIN_NAME' => '',
                                    'PORTAL_DESCRIPTION' => '',
                                    'PORTAL_CONTACT_EMAIL' => 'ユーザーからの問い合わせはこのメールアドレスに届きます',
                                    'PORTAL_STUDENT_ID_NAME' =>
                                        '例 : 学籍番号、学生番号、学生証番号、学生教職員番号など ・ 個人ごとに割り振られる番号の呼び方を指定します',
                                    'PORTAL_UNIVEMAIL_NAME' => '例 : 学生用メールアドレス、全学メールアドレス、大学のメールアドレスなど',
                                    'PORTAL_UNIVEMAIL_DOMAIN_PART' =>
                                        '例 : ed.tus.ac.jp ・ ユーザーがポータルにユーザー登録するには、アットマーク(@)以降がこの文字列となっているメールアドレスをユーザーが所有している必要があります。ユーザーごとにドメインが異なる場合、縦棒(|)で区切って複数のドメインを指定できます。',
                                    'PORTAL_UNIVEMAIL_LOCAL_PART' =>
                                        '学校発行メールアドレスにおいて、アットマーク(@)より前の文字列が下記のうちのどちらに当てはまるかを指定します',
                                ][$key] }}
                            </template>
                            @if ($key === 'PORTAL_UNIVEMAIL_LOCAL_PART')
                                <div class="form-radio">
                                    <label class="form-radio__label">
                                        <input class="form-radio__input" type="radio" name="{{ $key }}"
                                            id="localPartRadios1" value="student_id"
                                            {{ old($key, $value ?? '') === 'student_id' ? 'checked' : '' }}>
                                        <strong>学籍番号</strong><br>
                                        <span class="text-muted">アットマーク(@)より前に、学籍番号以外の文字列が含まれない場合のみ、こちらを選択してください</span>
                                    </label>
                                    <label class="form-radio__label">
                                        <input class="form-radio__input" type="radio" name="{{ $key }}"
                                            id="localPartRadios2" value="user_id"
                                            {{ old($key, $value ?? '') === 'user_id' ? 'checked' : '' }}>
                                        <strong>学籍番号ではない文字列</strong><br>
                                        <span class="text-muted">アットマーク(@)より前の文字列が学籍番号ではない場合は、こちらを選択してください</span>
                                    </label>
                                </div>
                            @else
                                <input id="{{ $key }}" type="text"
                                    class="form-control @error($key) is-invalid @enderror" name="{{ $key }}"
                                    value="{{ old($key, $key === 'APP_URL' && empty($value) ? $suggested_app_url : $value) }}"
                                    {{ $key !== 'PORTAL_DESCRIPTION' ? 'required' : '' }}>
                            @endif
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
