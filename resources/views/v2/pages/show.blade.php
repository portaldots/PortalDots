@extends('v2.layouts.app')

@section('title', $page->title)
    
@section('navbar')
    <app-nav-bar-back href="{{ route('pages.index') }}">
        お知らせ
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header>
        <template v-slot:title>{{ $page->title }}</template>
        <div class="text-muted">
            @datetime($page->updated_at) 更新
        </div>
    </app-header>
    <app-container class="markdown py-spacing-lg">
        @markdown($page->body)
    </app-container>
@endsection
