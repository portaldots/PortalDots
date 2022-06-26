@extends('layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    @include('includes.install_header')

    <app-container medium>
        <list-view>
            <list-view-card class="text-center markdown">
                <h2>
                    PortalDots へようこそ！
                </h2>
                <p>
                    はじめまして！<br>
                    これから 4 ステップに分けて、PortalDots の設定をしましょう。
                </p>
                <steps-list>
                    <steps-list-item>
                        ポータルの名前<br>
                        の設定
                    </steps-list-item>
                    <steps-list-item>
                        データベース<br>
                        の設定
                    </steps-list-item>
                    <steps-list-item>
                        メール配信<br>
                        の設定
                    </steps-list-item>
                    <steps-list-item>
                        管理者ユーザー<br>
                        の設定
                    </steps-list-item>
                </steps-list>
                <div>
                    <a href="{{ route('install.portal.edit') }}" class="btn is-primary is-wide">
                        <strong>設定をはじめる</strong>
                    </a>
                </div>
            </list-view-card>
        </list-view>
        <list-view>
            <template v-slot:title>インストールのサポート</template>
            <template v-slot:description>PortalDotsのインストールでわからないことがあれば、下記のお問い合わせ先までお気軽にお問い合わせください。</template>
            <list-view-card class="markdown">
                <ul>
                    <li>LINE 公式アカウント: <a href="https://lin.ee/aeee9s9" target="_blank">@aeee9s9</a></li>
                    <li>メールアドレス: <a href="mailto:support@portaldots.com" target="_blank">support@portaldots.com</a></li>
                    <li>Twitter: <a href="https://twitter.com/PortalDots" target="_blank">@PortalDots</a></li>
                </ul>
                <p>※PortalDots開発チームはボランティアによる活動です。こちらの連絡手段でのサポートは解決を保証するものではありません。予めご了承ください。</p>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
