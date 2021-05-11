@extends('layouts.app')

@section('title', 'アクティビティログ')

@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>
                アクティビティログ
                <app-badge muted small>BETA</app-badge>
            </template>
            <list-view-card>
                <p>「アクティビティログ」では、{{ config('app.name') }}内で行われた各種データ操作の履歴を確認できます。</p>
                <ul>
                    <li>参加登録を提出していない企画情報や、ユーザー情報(一部)の編集履歴は記録していません。</li>
                    <li>タグの紐付けや、申請フォームの設問の編集など、一部の履歴は記録していませんが、これらの履歴の記録は今後対応予定です。</li>
                    <li>回答の編集履歴など、編集前後の回答の比較ができない履歴があります。編集前後の比較ができるよう、今後対応予定です。</li>
                    <li>「アクティビティログ」機能に関するご意見などありましたら、 PortalDots 開発チームへお寄せください。</li>
                </ul>
            </list-view-card>
        </list-view>
        <list-view>
            @foreach ($activity_log as $activity)
                @php
                    $badgeProps = 'primary';
                    if ($activity->description === 'created' || $activity->description === 'submitted') {
                        $badgeProps = 'success';
                    } elseif ($activity->description === 'updated') {
                        $badgeProps = 'success outline';
                    } elseif ($activity->description === 'deleted') {
                        $badgeProps = 'danger';
                    }
                @endphp
                <list-view-card>
                    <details>
                        <summary>
                            <app-badge strong {{ $badgeProps }}>{{ $activity->description }}</app-badge>
                            <strong>{{ $activity->log_name }}</strong>
                            —
                            <strong>
                                @if (isset($activity->causer))
                                    {{ $activity->causer['name_family'] }}
                                    {{ $activity->causer['name_given'] }}
                                    ({{ $activity->causer['student_id']}})
                                @else
                                    未ログインユーザーまたは削除されたユーザー
                                @endif
                            </strong>
                            <div class="text-muted">@datetime($activity->updated_at)</div>
                        </summary>
                        <div class="markdown">
                            <pre><code>{{ json_encode($activity->changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) }}</code></pre>
                        </div>
                    </details>
                </list-view-card>
            @endforeach
            @if ($activity_log->hasPages())
                <list-view-pagination prev="{{ $activity_log->previousPageUrl() }}" next="{{ $activity_log->nextPageUrl() }}" />
            @endif
        </list-view>
    </app-container>
@endsection
