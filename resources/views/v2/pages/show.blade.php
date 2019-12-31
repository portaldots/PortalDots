@extends('v2.layouts.app')

@section('title', $page->title)

@section('navbar')
<a href="{{ route('pages.index') }}" class="navbar-back">
    <i class="fas fa-chevron-left navbar-back__icon"></i>
    お知らせ
</a>
@endsection

@section('content')
<header class="header">
    <div class="container">
        <h1 class="header__title">
            {{ $page->title }}
        </h1>
        <p class="header__date">
            @datetime($page->updated_at) 更新
        </p>
    </div>
</header>
<main class="container">
    <div class="markdown">
        @markdown($page->body)
    </div>
</main>
@endsection
