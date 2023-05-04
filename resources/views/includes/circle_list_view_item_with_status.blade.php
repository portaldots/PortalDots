@isset($circle->participationType)
    {{-- 参加種別がない企画は参加登録機能で作成できないので、ここでステータスを表示できるようにする必要はない --}}
    @if (!$circle->hasSubmitted() && $circle->canSubmit() && Auth::user()->isLeaderInCircle($circle))
        <list-view-item href="{{ route('circles.confirm', ['circle' => $circle]) }}">
            <template v-slot:title>
                <span class="text-primary">
                    📮
                    ここをクリックして「{{ $circle->name }}」の参加登録を提出しましょう！
                </span>
                <div class="pull-right">
                    <app-badge muted>{{ $circle->participationType->name }}</app-badge>
                </div>
            </template>
            <template v-slot:meta>
                学園祭係(副責任者)の招待が完了しました。ここをクリックして登録内容に不備がないかどうかを確認し、参加登録を提出しましょう。
            </template>
            <p class="text-small">
                @datetime($circle->participationType->form->close_at) までに提出してください
            </p>
        </list-view-item>
    @elseif ($circle->isPending())
        <list-view-item href="{{ route('circles.show', ['circle' => $circle]) }}">
            <template v-slot:title>
                💭
                「{{ $circle->name }}」の参加登録の内容を確認中です
                <div class="pull-right">
                    <app-badge muted>{{ $circle->participationType->name }}</app-badge>
                </div>
            </template>
            <template v-slot:meta>
                ただいま参加登録の内容を確認しています。{{ config('portal.admin_name') }}より指示がある場合は従ってください。また、内容確認のためご連絡を差し上げる場合がございます。
            </template>
        </list-view-item>
    @elseif (!$circle->hasSubmitted() && !$circle->canSubmit() && Auth::user()->isLeaderInCircle($circle))
        <list-view-item href="{{ route('circles.users.index', ['circle' => $circle]) }}">
            <template v-slot:title>
                <span class="text-primary">
                    📩
                    ここをクリックして「{{ $circle->name }}」の学園祭係(副責任者)を招待しましょう！
                </span>
                <div class="pull-right">
                    <app-badge muted>{{ $circle->participationType->name }}</app-badge>
                </div>
            </template>
            <template v-slot:meta>
                参加登録を提出するには、ここをクリックして学園祭係(副責任者)を招待しましょう。
            </template>
            <p class="text-small">
                @datetime($circle->participationType->form->close_at) までに提出してください
            </p>
        </list-view-item>
    @elseif ($circle->hasApproved())
        <list-view-item href="{{ route('circles.show', ['circle' => $circle]) }}">
            <template v-slot:title>
                🎉
                「{{ $circle->name }}」の参加登録は受理されました
                <div class="pull-right">
                    <app-badge muted>{{ $circle->participationType->name }}</app-badge>
                </div>
            </template>
        </list-view-item>
    @elseif ($circle->hasRejected())
        <list-view-item href="{{ route('circles.show', ['circle' => $circle]) }}">
            <template v-slot:title>
                <span class="text-danger">
                    ⚠️
                    「{{ $circle->name }}」の参加登録は受理されませんでした
                </span>
                <div class="pull-right">
                    <app-badge muted>{{ $circle->participationType->name }}</app-badge>
                </div>
            </template>
            <template v-slot:meta>
                @isset($circle->status_reason)
                    詳細はこちら
                @endisset
            </template>
        </list-view-item>
    @elseif (!Auth::user()->isLeaderInCircle($circle))
        <list-view-item href="{{ route('circles.show', ['circle' => $circle]) }}">
            <template v-slot:title>
                <span class="text-primary">
                    📄
                    ここをクリックすると「{{ $circle->name }}」の参加登録の内容を確認できます
                </span>
                <div class="pull-right">
                    <app-badge muted>{{ $circle->participationType->name }}</app-badge>
                </div>
            </template>
        </list-view-item>
    @endif
@endisset
