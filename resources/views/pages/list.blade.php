@extends('layouts.app')

@section('title', 'お知らせ')

@section('content')
    <app-container>
        <form class="pt-spacing form is-search" method="get" action="{{ url()->full() }}">
            <input class="form-control" type="search" name="query" placeholder="お知らせを検索" value="{{ old('query', $searchQuery) }}">
            <button class="btn is-secondary is-no-shadow">検索</button>
        </form>

        @isset ($searchQuery)
            <div class="pt-spacing-sm">
                <a href="{{ URL::current() }}" class="text-muted">
                    <strong>
                        <i class="fas fa-times-circle"></i>
                        検索をリセット
                    </strong>
                </a>
            </div>
        @endisset

        @if ($pages->isEmpty())
            @empty ($searchQuery)
                <list-view-empty icon-class="fas fa-bullhorn" text="お知らせはまだありません"></list-view-empty>
            @else
                <list-view-empty icon-class="fas fa-search" text="検索結果が見つかりませんでした">
                    <p>入力するキーワードを変えて、再度検索をお試しください。</p>
                    <p>
                        <a href="{{ URL::current() }}" class="btn is-primary">
                            <i class="fas fa-times-circle"></i>
                            検索をリセット
                        </a>
                    </p>
                </list-view-empty>
            @endempty
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
