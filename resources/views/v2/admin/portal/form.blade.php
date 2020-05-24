@extends('v2.layouts.no_drawer')

@section('title', 'PortalDots の設定')

@section('navbar')
    <app-nav-bar-back href="{{ url('home_staff') }}" data-turbolinks="false">
        スタッフモード
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header container-medium>
        <template v-slot:title>
            ポータル情報の設定
        </template>
    </app-header>

    <form method="post" action="{{ route('admin.portal.update') }}">
        @csrf

        @method('patch')

        <app-container medium>
            <list-view>
                <template v-slot:title>ポータル情報の設定</template>
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
                @endforeach
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
