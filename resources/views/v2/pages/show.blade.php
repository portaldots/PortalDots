@extends('v2.layouts.app')

@section('title', $page->title)

@section('navbar')
<a href="{{ route('pages.index') }}" class="navbar-back">
    <i class="fas fa-chevron-left navbar-back__icon"></i>
    お知らせ
</a>
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
