@extends('layouts.app')

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
    <app-container data-turbolinks="false" class="markdown py-spacing-lg">
        @markdown($page->body)
    </app-container>
    @if (count($page->documents) > 0)
        <app-container>
            <list-view>
                <template v-slot:title>関連する配布資料</template>

                @foreach ($page->documents as $document)
                    <list-view-item href="{{ route('documents.show', ['document' => $document]) }}" newtab>
                        <template v-slot:title>
                            @if ($document->is_important)
                                <i class="fas fa-exclamation-circle fa-fw text-danger"></i>
                            @else
                                <i class="far fa-file-alt fa-fw"></i>
                            @endif
                            {{ $document->name }}
                        </template>
                        <template v-slot:meta>
                            @datetime($document->updated_at) 更新
                            <br>
                            {{ strtoupper($document->extension) }}ファイル
                            •
                            @filesize($document->size)
                        </template>
                        {{ $document->description }}
                    </list-view-item>
                @endforeach
            </list-view>
        </app-container>
    @endif
@endsection
