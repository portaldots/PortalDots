@extends('v2.layouts.app')

@section('title', 'お知らせ')
    
@section('content')
    <app-container>
        @if ($pages->isEmpty())
            <list-view-empty icon-class="fas fa-bullhorn" text="お知らせはまだありません" />
        @else
            <list-view>
                @foreach ($pages as $page)
                    <list-view-item href="{{ route('pages.show', $page) }}">
                        <template v-slot:title>
                            {{ $page->title }}
                            @if ($page->isNew())
                                <span class="badge is-danger">NEW</span>
                            @endif
                        </template>
                        <template v-slot:meta>
                            @datetime($page->updated_at)
                        </template>
                        @summary($page->body)
                    </list-view-item>
                @endforeach
                @if ($pages->hasPages())
                    @if ($pages->previousPageUrl())
                        <list-view-item href="{{ $pages->previousPageUrl() }}">
                            <template v-slot:title>前のページへ</template>
                        </list-view-item>
                    @endif
                    @if ($pages->nextPageUrl())
                        <list-view-item href="{{ $pages->nextPageUrl() }}">
                            <template v-slot:title>次のページへ</template>
                        </list-view-item>
                    @endif
                @endif
            </list-view>
        @endif
    </app-container>
@endsection
