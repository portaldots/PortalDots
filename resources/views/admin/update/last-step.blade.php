@extends('layouts.no_drawer')

@section('title', 'PortalDots のアップデート')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    @include('includes.updater_header')

    <form-with-confirm action="{{ route('admin.update.run') }}" method="post"
        confirm-message="アップデートが完了するまで、画面をそのままにしてください。
アップデートを実行します。よろしいですか？">
        @csrf

        <app-container medium>
            <list-view>
                <template v-slot:title>アップデートの最終確認</template>
                <list-view-card>
                    <p>PortalDots を<strong>バージョン {{ $current_version_info->getFullVersion() }}</strong> から<strong>バージョン {{ $latest_release->getVersion()->getFullVersion() }}</strong> にアップデートします。</p>
                    <p>この操作は取り消せません。</p>
                    <app-info-box danger>
                        アップデート中、画面表示が固まったように見えることがありますが、<strong>「アップデートが完了しました」と表示されるまで、画面を閉じないでください。</strong>
                    </app-info-box>
                </list-view-card>
            </list-view>
            <div class="text-center pt-spacing-md pb-spacing">
                <a href="{{ route('admin.update.before-update') }}" class="btn is-secondary">
                    <i class="fas fa-chevron-left"></i>
                    戻る
                </a>
                <button type="submit" class="btn is-primary is-wide">
                    <strong>アップデートを実行</strong>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </app-container>
    </form-with-confirm>
@endsection
