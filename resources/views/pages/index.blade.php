@extends('layouts.app')

@section('title', 'お知らせ')

@section('content')
    <app-container>
        @if ($pages->isEmpty())
            <list-view-empty icon-class="fas fa-bullhorn" text="お知らせはまだありません" />
        @else
            <list-view>
                @foreach ($pages as $page)
                    <list-view-item
                        href="{{ route('pages.show', $page) }}"
                        {{ Auth::check() && $page->usersWhoRead->isEmpty() ? 'unread' : '' }}
                    >
                        <template v-slot:title>
                            @if (!$page->viewableTags->isEmpty())
                                <app-badge primary outline>限定公開</app-badge>
                            @else
                                <app-badge muted outline>全員に公開</app-badge>
                            @endif
                            {{ $page->title }}
                            @if ($page->isNew())
                                <app-badge danger>NEW</app-badge>
                            @endif
                        </template>
                        <template v-slot:meta>
                            @datetime($page->updated_at)
                        </template>
                        @summary($page->body)
                    </list-view-item>
                @endforeach
                @if ($pages->hasPages())
                    <list-view-pagination prev="{{ $pages->previousPageUrl() }}" next="{{ $pages->nextPageUrl() }}" />
                @endif
            </list-view>
        @endif
    </app-container>
@endsection
