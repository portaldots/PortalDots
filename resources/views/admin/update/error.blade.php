@extends('layouts.no_drawer')

@section('title', 'PortalDots のアップデート')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>エラー</template>
            <list-view-card class="markdown">
                <p>アップデート中にエラーが発生しました。</p>
                <p>PortalDots 開発チームに、下記のエラー内容をお送りください。</p>
                <h2>エラー内容</h2>
                <pre>{{ $error }}</pre>
                <h2>PortalDots開発チームへのお問い合わせ先</h2>
                <p>※PortalDots開発チームはボランティアによる活動です。こちらの連絡手段でのサポートは解決を保証するものではありません。予めご了承ください。</p>
                <ul>
                    <li>LINE 公式アカウント: <a href="https://lin.ee/aeee9s9" target="_blank">@aeee9s9</a></li>
                    <li>メールアドレス: <a href="mailto:support@portaldots.com" target="_blank">support@portaldots.com</a></li>
                    <li>Twitter: <a href="https://twitter.com/PortalDots" target="_blank">@PortalDots</a></li>
                </ul>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
