@extends('layouts.no_drawer')

@section('title', 'PortalDots のアップデート')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    @include('includes.updater_header')

    <app-container medium>
        <list-view>
            <template v-slot:title>アップデートの前に</template>
            <list-view-card no-border>
                <app-info-box danger>
                    <strong>アップデートがうまくいかなかった場合に備えるために、下記の事項は必ずお読みください。</strong>
                </app-info-box>
                <div class="markdown pt-spacing-md">
                    <h2>PortalDots のバックアップをとる</h2>
                    <p>アップデートに失敗した際、PortalDots をアップデート前の状態の復元できるよう、あらかじめバックアップをとっておきましょう。</p>
                    <ul>
                        <li>FTP ソフトを使って、サーバー上にある PortalDots ファイルを全てダウンロードしておきましょう。</li>
                        <li>データベース管理ツール（phpMyAdmin など）を使って、データベースのバックアップをダウンロードしておきましょう。</li>
                    </ul>
                    <p>バックアップ内のファイルには個人情報が多く含まれています。アップデートが正常に完了したことを確認した場合は速やかにバックアップを削除するなど、<strong>バックアップの取り扱いには十分注意してください。</strong></p>
                    <h2>PortalDots 開発チームへの連絡手段をメモしておく</h2>
                    <p>PortalDots 開発チームのお問い合わせ先は下記の通りです。LINE 公式アカウントを友だち登録しておくことで、もしもの際にすぐサポートを受けることができます。</p>
                    <p>※PortalDots開発チームはボランティアによる活動です。こちらの連絡手段でのサポートは解決を保証するものではありません。予めご了承ください。</p>
                    <ul>
                        <li>LINE 公式アカウント: <a href="https://lin.ee/aeee9s9" target="_blank">@aeee9s9</a></li>
                        <li>メールアドレス: <a href="mailto:support@portaldots.com" target="_blank">support@portaldots.com</a></li>
                        <li>Twitter: <a href="https://twitter.com/PortalDots" target="_blank">@PortalDots</a></li>
                    </ul>
                </div>
            </list-view-card>
        </list-view>
        <div class="text-center pt-spacing-md pb-spacing">
            <p class="pb-spacing-sm">
                上記の事項を全て読んだら、[最終確認に進む] をクリックしてください。
            </p>
            <a href="{{ route('admin.update.index') }}" class="btn is-secondary">
                <i class="fas fa-chevron-left"></i>
                戻る
            </a>
            <a href="{{ route('admin.update.last-step') }}" class="btn is-primary is-wide">
                最終確認に進む
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </app-container>
@endsection
