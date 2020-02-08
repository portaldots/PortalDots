@extends('v2.layouts.no_drawer')

@section('title', 'メール認証完了')

@section('content')
<app-container medium>
    <list-view header-title="メール認証完了">
        <list-view-item>
            <p class="text-center text-success">
                <i class="fas fa-check-circle"></i>
                <strong>手続きが完了しました！</strong>
            </p>
            <p class="text-center">
                {{-- リンク先が Turbolinks に対応したら data-turbolinks="false" は削除する --}}
                <a href="{{ url('/') }}" class="btn is-primary" data-turbolinks="false">
                    ダッシュボードにアクセス
                </a>
            </p>
        </list-view-item>
    </list-view>
</app-container>
@endsection
