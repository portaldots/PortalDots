@extends('layouts.no_drawer')

@section('title', 'PortalDotsについて')

@prepend('css')
<style>
.logo-wrapper {
    margin: 0 0 1rem;
}
.logo {
    display: block;
    width: 100%;
    max-width: 240px;
    height: auto;
    margin: 0 auto;
}
</style>
@endprepend

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>PortalDotsについて</template>
            <list-view-card class="text-center">
                <h1 class="logo-wrapper">
                    <img src="{{ mix('/images/portalDotsLogo.svg') }}" class="logo" alt="PortalDots">
                </h1>
                <div>バージョン {{ $current_version_info->getFullVersion() }}</div>
                @if (isset($latest_release) && !$current_version_info->equals($latest_release->getVersion()))
                    <div class="text-success">
                        <strong>
                            <i class="fas fa-info-circle"></i>
                            バージョン {{ $latest_release->getVersion()->getFullVersion() }} にアップデートできます。
                        </strong>
                    </div>
                @else
                    <div class="text-muted">
                        <strong>
                            <i class="far fa-check-circle"></i>
                            お使いのバージョンは最新です。
                        </strong>
                    </div>
                @endif
            </list-view-card>
            <list-view-card>
                PortalDots(ポータルドット)は、学園祭実行委員会と参加企画担当者との間のコミュニケーションを支援するウェブシステムです。お知らせメールの一斉送信や各種申請の受付をオンラインで簡単に行うことができます。
            </list-view-card>
            <list-view-action-btn href="https://dots.soji.dev" target="_blank" rel="noopener">
                PortalDots 公式ウェブサイト
                <i class="fas fa-external-link-alt fa-fw"></i>
            </list-view-action-btn>
        </list-view>

        @if (isset($latest_release) && !$current_version_info->equals($latest_release->getVersion()))
            <list-view>
                <template v-slot:title>アップデート</template>
                <template v-slot:description>PortalDots を最新バージョンにアップデートできます。PortalDots 管理者ユーザーは、このページで PortalDots のアップデート方法を確認できます。</template>
                @if (Auth::user()->is_admin)
                    <list-view-card>
                        <p>PortalDots を最新バージョンにアップデートするには、以下の方法に従ってください。</p>
                        <ol>
                            <li>PortalDots データベースとファイルをバックアップします。</li>
                            <li>以下のリンクより、PortalDots バージョン {{ $latest_release->getVersion()->getFullVersion() }} の ZIP ファイルをダウンロードします。</li>
                            <li>ZIP ファイルを解凍します。</li>
                            <li>
                                以下のフォルダおよびファイルをサーバーへ上書きアップロードします。<strong>これら以外のファイルをアップロードすると、{{ config('app.name') }}や保存データが破損する可能性があります。</strong>
                                <ul>
                                    <li>artisan ファイル</li>
                                    <li>app フォルダ</li>
                                    <li>application フォルダ</li>
                                    <li>bootstrap フォルダ</li>
                                    <li>config フォルダ</li>
                                    <li>database フォルダ</li>
                                    <li>resources フォルダ</li>
                                    <li>routes フォルダ</li>
                                    <li>system フォルダ (※)</li>
                                    <li>vendor フォルダ</li>
                                    <li>public フォルダ内の assets フォルダ内にある grocery_crud フォルダ (※)</li>
                                    <li>public フォルダ内の common フォルダ (※)</li>
                                    <li>public フォルダ内の css フォルダ</li>
                                    <li>public フォルダ内の fonts フォルダ</li>
                                    <li>public フォルダ内の images フォルダ</li>
                                    <li>public フォルダ内の js フォルダ</li>
                                    <li>public フォルダ内の index_laravel.php ファイル (※)</li>
                                    <li>public フォルダ内の index.php ファイル</li>
                                    <li>public フォルダ内の mix-manifest.json ファイル</li>
                                </ul>
                            </li>
                            <li>アップロード完了後、<a href="{{ url('/') }}" target="_blank" rel="noopener">{{ config('app.name') }}</a>にアクセスし、正常に動作するか確認を行ってください。</li>
                        </ol>
                        <p>
                            (※) で示したフォルダやファイルは、PortalDots のバージョンによっては ZIP ファイルに含まれていない場合があります。ZIP ファイルに含まれていないが、サーバー上には存在するフォルダ・ファイルは、サーバーから削除してください。
                        </p>
                    </list-view-card>
                    <list-view-action-btn href="{{ $latest_release->getBrowserDownloadUrl() }}" icon-class="far fa-file-archive" data-turbolinks="false">
                        PortalDots バージョン {{ $latest_release->getVersion()->getFullVersion() }} をダウンロード(@filesize($latest_release->getSize()))
                    </list-view-action-btn>
                @else
                    <list-view-card>
                        <list-view-empty text="アップデートは管理者ユーザーが行います。">
                            <p>
                                詳細については、{{ config('portal.admin_name') }}で{{ config('app.name') }}を管理している方へお問い合わせください。
                            </p>
                        </list-view-empty>
                    </list-view-card>
                @endif
            </list-view>
            <list-view>
                <template v-slot:title>バージョン {{ $latest_release->getVersion()->getFullVersion() }} の詳細</template>
                <list-view-card data-turbolinks="false" class="markdown">
                    @markdown($latest_release->getBody())
                </list-view-card>
            </list-view>
        @endif
    </app-container>
@endsection
