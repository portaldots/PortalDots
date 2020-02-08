@extends('v2.layouts.app')

@section('title', 'お知らせ')

@section('content')
<app-container>
    <list-view>
        @foreach ($pages as $page)
        <list-view-item href="{{ route('pages.show', $page) }}">
            <template v-slot:title>
                {{ $page->title }}
            </template>
            <template v-slot:meta>
                @datetime($page->updated_at)
            </template>
            @summary($page->body)
        </list-view-item>
        @endforeach
        @empty ($pages)
        <list-view-empty
            icon-class="fas fa-bullhorn"
            text="お知らせはまだありません"
        />
        @endempty
    </list-view>
</app-container>
@endsection
