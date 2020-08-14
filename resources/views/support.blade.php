@extends('layouts.app')

@section('no_circle_selector', true)

@section('title', '推奨動作環境')

@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>ブラウザ環境について</template>
            <list-view-card>
                <p>{{ config('app.name') }}は以下の環境での利用を推奨しています。</p>
                <ul>
                    <li>Microsoft Edge 最新版</li>
                    <li>Mozilla Firefox 最新版</li>
                    <li>Google Chrome 最新版</li>
                    <li>Safari 最新版</li>
                </ul>
                <p>推奨環境以外で利用された場合や、推奨環境下でもご利用のブラウザの設定等によっては、正しく表示されない場合がありますのでご了承ください。</p>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
