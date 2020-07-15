@extends('layouts.app')

@section('no_circle_selector', true)

@section('title', '推奨動作環境')

@push('css')
<style>
.listview-item__title {
  font-size: 1.8rem!important;
}
</style>
@endpush


@section('content')
<app-container>
  <list-view>
    <list-view-item>
      <template v-slot:title>ブラウザ環境について</template>
      <p>当サイトPortalDotsでは以下の環境でご覧いただくことを推奨いたします。</p>
      <ul>
        <li>Microsoft Edge 最新版</li>
        <li>Mozilla Firefox 最新版</li>
        <li>Google Chrome 最新版（Windows 10以上, Android 8.0以上）</li>
        <li>Safari 最新版</li>
      </ul>
      <p>推奨環境以外で利用された場合や、推奨環境下でもご利用のブラウザの設定等によっては、正しく表示されない場合がありますのでご了承ください。</p>
    </list-view-item>
  </list-view>
</app-container>
@endsection
