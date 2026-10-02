@extends('layouts.app')

@section('no_circle_selector', true)

@section('title', term('document'))

@section('content')
    <app-container>
        @if ($documents->isEmpty())
            <list-view-empty icon-class="far fa-file-alt" text="{{ term('document') }}はまだありません" />
        @else
            <list-view>
                @foreach ($documents as $document)
                    <list-view-item>
                        <template v-slot:title>
                            <a href="{{ route('documents.show', ['document' => $document]) }}" target="_blank"
                                rel="noopener noreferrer">
                                @if ($document->is_important)
                                    <i class="fas fa-exclamation-circle fa-fw text-danger"></i>
                                @else
                                    <i class="far fa-file-alt fa-fw"></i>
                                @endif
                                {{ $document->name }}
                            </a>
                            @if ($document->isNew())
                                <app-badge danger>NEW</app-badge>
                            @endif
                            @if ($document->approvals->isNotEmpty())
                                @php $approval = $document->approvals->first(); @endphp
                                <a href="{{ route('documents.approval.show', ['document' => $document]) }}">
                                    @if ($approval->status === \App\Eloquents\DocumentApproval::STATUS_APPROVED)
                                        <app-badge success>{{ $approval->circleStatusLabel() }}</app-badge>
                                    @elseif ($approval->status === \App\Eloquents\DocumentApproval::STATUS_CHANGES_REQUESTED)
                                        <app-badge danger>{{ $approval->circleStatusLabel() }}</app-badge>
                                    @else
                                        <app-badge primary>{{ $approval->circleStatusLabel() }}</app-badge>
                                    @endif
                                </a>
                            @endif
                        </template>
                        <template v-slot:meta>
                            @datetime($document->updated_at) 更新
                            <br>
                            {{ strtoupper($document->extension) }}ファイル
                            •
                            @filesize($document->size)
                            @if ($document->versions->count() > 1)
                                <br>
                                第{{ $document->versions->first()->version }}版
                                @foreach ($document->versions->skip(1) as $version)
                                    ・<a
                                        href="{{ route('documents.versions.show', ['document' => $document, 'version' => $version]) }}"
                                        target="_blank" rel="noopener noreferrer">第{{ $version->version }}版</a>
                                @endforeach
                            @endif
                        </template>
                        {{ $document->description }}
                    </list-view-item>
                @endforeach
                @if ($documents->hasPages())
                    <list-view-pagination prev="{{ $documents->previousPageUrl() }}"
                        next="{{ $documents->nextPageUrl() }}" />
                @endif
            </list-view>
        @endif
    </app-container>
@endsection
