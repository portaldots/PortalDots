@extends('layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    @include('includes.install_header')

    <app-container medium>
        <list-view>
            <list-view-card class="text-center">
                <p class="text-muted">
                    <i class="fas fa-door-open fa-2x"></i>
                </p>
                <p>
                    <strong>PortalDots へようこそ！</strong>
                </p>
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
                <div class="pt-spacing">
                    <a href="{{ route('install.portal.edit') }}" class="btn is-primary is-wide">
                        <strong>設定をはじめる</strong>
                    </a>
                </div>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
