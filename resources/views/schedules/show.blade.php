@extends('layouts.app')

@section('title', $schedule->name)

@section('no_circle_selector', true)

@section('navbar')
    <app-nav-bar-back href="{{ route('schedules.index') }}">
        スケジュール
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header>
        <template v-slot:title>
            {{ $schedule->name }}
        </template>
        <div class="text-muted">
            @datetime($schedule->start_at)〜 • {{ $schedule->place }}
        </div>
    </app-header>
    <app-container class="py-spacing-lg">
        <div class="markdown">
            @markdown($schedule->description)
        </div>
    </app-container>
    @if (count($schedule->documents) > 0)
        <app-container>
            <list-view>
                <template v-slot:title>配布資料</template>

                @foreach ($schedule->documents as $document)
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
                            @isset($document->schedule)
                                •
                                {{ $document->schedule->name }}で配布
                            @endisset
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
