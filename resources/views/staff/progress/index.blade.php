@extends('layouts.app')

@section('title', '企画の進捗')

@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>企画の進捗（{{ $progresses->total() }}企画）</template>
            @if ($progresses->isEmpty())
                <list-view-empty icon-class="fas fa-star" text="承認済みの企画はありません"></list-view-empty>
            @else
                <div class="progress_table-wrapper">
                    <table class="progress_table">
                        <thead>
                            <tr>
                                <th>企画</th>
                                <th>進捗</th>
                                <th>次の期限</th>
                                <th>誰の番</th>
                                <th>遅れ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($progresses as $progress)
                                <tr>
                                    <td>
                                        <a href="{{ route('staff.circles.edit', ['circle' => $progress->getCircle()->id]) }}">
                                            {{ $progress->getCircle()->name }}
                                        </a>
                                    </td>
                                    <td data-label="進捗">{{ $progress->getProgressLabel() }}</td>
                                    <td data-label="次の期限">
                                        @if ($progress->getNextDueAt())
                                            @datetime($progress->getNextDueAt())
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="誰の番">
                                        @if ($progress->getTurn() === \App\Services\Circles\ValueObjects\CircleProgress::TURN_STAFF)
                                            <app-badge danger>{{ $progress->getTurn() }}</app-badge>
                                        @else
                                            {{ $progress->getTurn() }}
                                        @endif
                                    </td>
                                    <td data-label="遅れ">
                                        @if ($progress->getOverdueCount() > 0)
                                            <app-badge danger>{{ $progress->getOverdueCount() }}件</app-badge>
                                        @else
                                            なし
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($progresses->hasPages())
                    <list-view-pagination prev="{{ $progresses->previousPageUrl() }}" next="{{ $progresses->nextPageUrl() }}" />
                @endif
            @endif
        </list-view>

        <list-view>
            <template v-slot:title>スタッフの対応が必要</template>
            @if ($review_entries->isEmpty() && $needs_staff_threads->isEmpty())
                <list-view-empty icon-class="fas fa-check-circle" text="対応が必要な項目はありません"></list-view-empty>
            @else
                @foreach ($review_entries as $entry)
                    @php $unit = $entry['unit']; @endphp
                    @if ($unit->isType(\App\Services\Circles\ValueObjects\ProgressUnit::TYPE_FORM))
                        <list-view-item href="{{ route('staff.forms.answers.edit', ['form' => $unit->getForm()->id, 'answer' => $unit->getAnswer()->id]) }}">
                            <template v-slot:title>
                                <app-badge primary>要確認</app-badge>
                                {{ $entry['circle']->name }} — {{ $unit->getLabel() }}
                            </template>
                        </list-view-item>
                    @else
                        <list-view-item href="{{ route('staff.documents.edit', ['document' => $unit->getDocumentApproval()->document_id]) }}">
                            <template v-slot:title>
                                <app-badge danger>{{ $unit->getDocumentApproval()->staffStatusLabel() }}</app-badge>
                                {{ $entry['circle']->name }} — {{ $unit->getLabel() }}
                            </template>
                        </list-view-item>
                    @endif
                @endforeach
                @foreach ($needs_staff_threads as $thread)
                    <list-view-item href="{{ route('staff.threads.show', ['thread' => $thread->id]) }}">
                        <template v-slot:title>
                            <app-badge danger>{{ $thread->staffStatusLabel() }}</app-badge>
                            {{ optional($thread->circle)->name }}
                        </template>
                    </list-view-item>
                @endforeach
            @endif
        </list-view>
    </app-container>
@endsection
