@extends('layouts.app')

@section('no_circle_selector', true)

@section('title', $document->name . ' — 確認依頼')

@section('navbar')
    <app-nav-bar-back href="{{ route('documents.index') }}">
        配布資料
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header>
        <template v-slot:title>{{ $document->name }}</template>
    </app-header>

    <app-container>
        <list-view>
            <list-view-item no-border>
                <template v-slot:title>
                    <a href="{{ route('documents.versions.show', ['document' => $document, 'version' => $approval->documentVersion]) }}"
                        target="_blank" rel="noopener noreferrer">
                        <i class="far fa-file-alt fa-fw"></i>
                        第{{ $approval->documentVersion->version }}版のファイルを見る
                    </a>
                </template>
            </list-view-item>
        </list-view>

        <list-view>
            <list-view-card>
                <p>
                    状況 :
                    @if ($approval->status === \App\Eloquents\DocumentApproval::STATUS_APPROVED)
                        <app-badge success>{{ $approval->circleStatusLabel() }}</app-badge>
                    @elseif ($approval->status === \App\Eloquents\DocumentApproval::STATUS_CHANGES_REQUESTED)
                        <app-badge danger>{{ $approval->circleStatusLabel() }}</app-badge>
                    @else
                        <app-badge primary>{{ $approval->circleStatusLabel() }}</app-badge>
                    @endif
                </p>
                @if ($approval->status === \App\Eloquents\DocumentApproval::STATUS_CHANGES_REQUESTED && !empty(optional($approval->decisions->first())->comment))
                    <p class="text-danger">
                        <i class="fas fa-info-circle"></i>
                        修正依頼の内容 : {{ $approval->decisions->first()->comment }}
                    </p>
                @endif
            </list-view-card>
        </list-view>

        @if ($approval->status !== \App\Eloquents\DocumentApproval::STATUS_APPROVED)
            <list-view>
                <template v-slot:title>この版を確認する</template>
                <list-view-form-group>
                    <template v-slot:label>この版の内容を確認しました</template>
                    <form method="post" action="{{ route('documents.approval.approve', ['document' => $document]) }}">
                        @csrf
                        <input type="hidden" name="document_version_id" value="{{ $approval->document_version_id }}">
                        <input type="hidden" name="lock_version" value="{{ $approval->lock_version }}">
                        <button type="submit" class="btn is-primary is-sm">確認済みにする</button>
                    </form>
                </list-view-form-group>
                <list-view-form-group label-for="comment">
                    <template v-slot:label>
                        修正を依頼する
                        <app-badge danger>コメント必須</app-badge>
                    </template>
                    <form method="post"
                        action="{{ route('documents.approval.request-changes', ['document' => $document]) }}">
                        @csrf
                        <input type="hidden" name="document_version_id" value="{{ $approval->document_version_id }}">
                        <input type="hidden" name="lock_version" value="{{ $approval->lock_version }}">
                        <textarea id="comment" class="form-control @error('comment') is-invalid @enderror" name="comment"
                            required>{{ old('comment') }}</textarea>
                        @error('comment')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn is-secondary is-sm">修正を依頼する</button>
                    </form>
                </list-view-form-group>
            </list-view>
        @endif

        <list-view>
            <template v-slot:title>これまでの経緯</template>
            @foreach ($approval->decisions as $decision)
                <list-view-item no-border>
                    <template v-slot:title>
                        第{{ $decision->documentVersion->version }}版
                        @if ($decision->status === \App\Eloquents\DocumentApproval::STATUS_APPROVED)
                            <app-badge success>{{ $decision->circleStatusLabel() }}</app-badge>
                        @elseif ($decision->status === \App\Eloquents\DocumentApproval::STATUS_CHANGES_REQUESTED)
                            <app-badge danger>{{ $decision->circleStatusLabel() }}</app-badge>
                        @else
                            <app-badge primary>{{ $decision->circleStatusLabel() }}</app-badge>
                        @endif
                    </template>
                    <template v-slot:meta>
                        @datetime($decision->created_at)
                        @if ($decision->decidedBy)
                            ・{{ $decision->decidedBy->name }}
                        @endif
                    </template>
                    {{ $decision->comment }}
                </list-view-item>
            @endforeach
        </list-view>
    </app-container>
@endsection
