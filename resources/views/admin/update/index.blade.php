@extends('layouts.no_drawer')

@section('title', 'PortalDots のアップデート')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    @include('includes.updater_header')

    <app-container medium>
        <list-view>
            <list-view-card>
                <div class="text-center">
                    <h2>PortalDots のアップデート</h2>
                    <p>PortalDots をアップデートすることで、不具合を解消し、{{ config('app.name') }}を常に安全な状態に保つことができます。</p>
                </div>
                <app-info-box danger>
                    <strong>このアップデート機能はベータ版です</strong><br>
                    自動アップデート機能はベータ版のため、動作が安定しない可能性があります。アップデート実行前は必ずファイルやデータベースのバックアップを取得してください。
                </app-info-box>
                <div class="pt-spacing text-center">
                    <a href="{{ route('admin.update.before-update') }}" class="btn is-primary is-wide">
                        <strong>はじめる</strong>
                    </a>
                </div>
            </list-view-card>
        </list-view>
        <list-view>
            <template v-slot:title>バージョン {{ $latest_release->getVersion()->getFullVersion() }} の詳細</template>
            <template v-slot:description>このアップデートを適用すると、下記の変更が行われます。</template>
            <list-view-card data-turbolinks="false" class="markdown">
                @markdown($latest_release->getBody())
            </list-view-card>
        </list-view>
    </app-container>
@endsection
