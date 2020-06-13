@extends('v2.layouts.app')

@section('no_circle_selector', true)

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
        @if (!$page->viewableTags->isEmpty())
            <div class="text-muted pt-spacing-sm">
                <app-badge primary outline>限定公開</app-badge>
                このお知らせは、限られた企画のメンバーのみ閲覧可能です。
            </div>
        @endif
    </app-header>
    <app-container class="markdown py-spacing-lg">
        @markdown($page->body)
    </app-container>
@endsection
