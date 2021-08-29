@extends('layouts.no_drawer')

@section('title', 'PortalDots のインストール')

@prepend('meta')
    <meta name="robots" content="noindex">
@endprepend

@section('content')
    @include('includes.install_header')

    <form method="POST" action="{{ route('install.admin.store') }}">
        @csrf

        <app-container medium>
            <list-view>
                <template v-slot:title>管理者ユーザーの作成</template>
                <template v-slot:description>「{{ config('app.name') }}」の全設定を変更することができる管理者ユーザーを作成します。</template>

                @include('includes.user_register_form')
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <a href="{{ route('install.mail.edit') }}" class="btn is-secondary">
                    <i class="fas fa-chevron-left"></i>
                    戻る
                </a>
                <button type="submit" class="btn is-primary is-wide">
                    <strong>
                        登録してインストール実行
                    </strong>
                </button>
                <p class="pt-spacing-md">
                    「登録してインストール実行」をクリックすると、データベースにPortalDotsのデータが保存され、管理者ユーザーが作成されます。<br>
                    管理者ユーザーは「ポータル情報」の設定と、自身のユーザー情報のみ、後で変更できます。
                </p>
            </div>
        </app-container>
    </form>
@endsection
