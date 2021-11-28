@extends('layouts.no_drawer')

@section('title', 'PortalDots のアップデート')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>エラー</template>
            <list-view-card>
                <p>アップデート中にエラーが発生しました。</p>
                <p>PortalDots 開発チームに、下記のエラー内容をお送りください。</p>
                <pre>{{ $error }}</pre>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
