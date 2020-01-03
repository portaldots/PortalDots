@extends('v2.layouts.app')

@section('title', $schedule->name)

@section('navbar')
<a href="{{ route('schedules.index') }}" class="navbar-back">
    <i class="fas fa-chevron-left navbar-back__icon"></i>
    スケジュール
</a>
@endsection

@section('content')
<header class="header">
    <div class="container">
        <h1 class="header__title">
            {{ $schedule->name }}
        </h1>
        <p class="header__date">
            @datetime($schedule->start_at)〜 • {{ $schedule->place }}
        </p>
    </div>
</header>
<main class="container pb-spacing-lg">
    <div class="markdown">
        @markdown($schedule->description)
    </div>
</main>
<div class="listview container">
    <div class="listview-header">
        配布資料
    </div>
    @foreach ($schedule->documents as $document)
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
