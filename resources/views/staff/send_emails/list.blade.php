@extends('layouts.app')

@section('title', 'メール配信設定')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.pages.index') }}">
        お知らせ管理
    </app-nav-bar-back>
@endsection

@section('content')
    @unless ($hasSentEmail)
        <top-alert type="danger" keep-visible>
            <template v-slot:title>
                メールの一斉配信に失敗しました
            </template>
            CRON が適切に設定されているかご確認ください
        </top-alert>
    @endunless
    <app-header container-medium>
        <template v-slot:title>
            メール配信設定
        </template>
    </app-header>

    <app-container medium>
        <list-view>
            <template v-slot:title>メールの一斉送信機能を利用するにはサーバー側の設定が必要です</template>
            <list-view-card>
                <p>
                    サーバーのCRON設定において、<code>php artisan schedule:run</code>
                    というコマンドが5分おきに実行されるように設定してください。
                </p>
                <p>
                    サーバー提供会社によっては、1時間より短い間隔でのCRON設定を認めていない場合があります。その場合、1時間おきに実行されるように設定してください。
                </p>
                <p class="text-muted">
                    このメッセージは、すでにサーバー側の設定が完了している場合であっても表示されます。
                </p>
            </list-view-card>
        </list-view>

        <list-view>
            <template v-slot:title>メールの配信をすべてキャンセル</template>
            <list-view-card>
                <p>間違えてメールを配信してしまった場合、メールの配信を全てキャンセル(停止)できます。</p>
                <p>キャンセルを行うタイミングが配信処理中だった場合、一部ユーザーにはメールが配信されてしまうことがあります。</p>
                <form method="post" action="{{ route('staff.send_emails') }}" class="mb-1">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn is-danger">
                        メールの配信を全てキャンセル
                    </button>
                </form>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
