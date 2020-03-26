@extends('v2.layouts.app')

@section('title', $schedule->name)
    
@section('navbar')
    <app-nav-bar-back href="{{ route('schedules.index') }}">
        スケジュール
    </app-nav-bar-back>
@endsection

@section('content')
    <header class="header">
        <app-container>
            <h1 class="header__title">
                {{ $schedule->name }}
            </h1>
            <p class="header__date">
                @datetime($schedule->start_at)〜 • {{ $schedule->place }}
            </p>
        </app-container>
    </header>
    <app-container component-is="main" class="pb-spacing-lg">
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
                        </template>
                        @summary($document->description)
                    </list-view-item>
                @endforeach
            </list-view>
        </app-container>
    @endif
@endsection
