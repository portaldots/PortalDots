@extends('v2.layouts.app')

@section('title', '配布資料')

@section('content')
<div class="listview">
    @foreach ($documents as $document)
    <a
        class="listview-item"
        href="{{ url("uploads/documents/{$document->id}") }}"
        target="_blank"
        rel="noopener"
    >
        <div class="listview-item__body">
            <p class="listview-item__title{{ $document->is_important ? ' text-danger' : '' }}">
                @if ($document->is_important)
                <i class="fas fa-exclamation-circle"></i>
                @else
                <i class="far fa-file-alt fa-fw"></i>
                @endif
                {{ $document->name }}
            </p>
            <p class="listview-item__meta">
                @datetime($document->updated_at) 更新
                @isset($document->schedule)
                •
                {{ $document->schedule->name }}で配布
                @endisset
            </p>
            <p class="listview-item__summary">{{ $document->description }}</p>
        </div>
    </a>
    @endforeach
</div>
@endsection
