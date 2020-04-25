@extends('v2.layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@section('content')
    @include('v2.includes.install_header')

    <app-container medium>
        <list-view>
            <list-view-card>
                <list-view-empty icon-class="fas fa-door-open" text="PortalDots へようこそ！">
                    <p>
                        はじめまして！<br>
                        これから 3 ステップに分けて、PortalDots の設定をしましょう。
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
                    </steps-list>
                    <div class="pt-spacing-lg">
                        <a href="{{ route('install.portal.edit') }}" class="btn is-primary is-wide">
                            <strong>インストールをはじめる</strong>
                        </a>
                    </div>
                </list-view-empty>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
