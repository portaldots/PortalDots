@extends('v2.layouts.app')

@section('title', 'お知らせ')

@section('content')
<div class="listview container">
    @foreach ($pages as $page)
    <a class="listview-item" href="{{ route('pages.show', $page) }}">
        <div class="listview-item__body">
            <p class="listview-item__title">
                {{ $page->title }}
            </p>
            <p class="listview-item__meta">
                @datetime($page->updated_at)
            </p>
            <p class="listview-item__summary">
                @summary($page->body)
            </p>
        </div>
    </a>
    @endforeach
    @empty ($pages)
    <div class="listview-empty">
        <i class="fas fa-bullhorn listview-empty__icon"></i>
        <p class="listview-empty__text">お知らせはまだありません</p>
    </div>
    @endempty
</div>
@endsection
