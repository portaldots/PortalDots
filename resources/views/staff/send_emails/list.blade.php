@extends('v2.layouts.no_drawer')

@section('title', 'メールの一斉送信')

@section('navbar')
    <app-nav-bar-back inverse href="{{ url('home_staff/pages') }}" data-turbolinks="false">
        お知らせ管理
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header container-medium>
        <template v-slot:title>
            メールの一斉送信
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

        <hr>

        <list-view>
            <template v-slot:title>配信するお知らせを選択</template>
            @if ($pages->isEmpty())
                <list-view-empty icon-class="fas fa-bullhorn" text="お知らせがありません">
                    メールを一斉送信するには、まずお知らせを作成してください
                </list-view-empty>
            @else
                @foreach ($pages as $page)
                    <list-view-card>
                        <details>
                            <summary>
                                {{ $page->title }}
                            </summary>
                            <form
                                method="post"
                                action="{{ route('staff.send_emails') }}"
                                class="px-spacing-lg py-spacing"
                                onsubmit="return confirm('全ユーザーにメールが配信されます。よろしいですか？')"
                            >
                                @csrf
                                <input type="hidden" name="page_id" value="{{ $page->id }}">
                                <button type="submit" class="btn is-primary">
                                    このお知らせを全ユーザーに一斉送信する
                                </button>
                            </form>
                            <div class="markdown px-spacing-lg pb-spacing">
                                {{ Illuminate\Mail\Markdown::parse($page->body) }}
                            </div>
                        </details>
                    </list-view-card>
                @endforeach
            @endif
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
