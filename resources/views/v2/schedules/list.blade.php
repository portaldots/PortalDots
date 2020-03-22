@extends('v2.layouts.app')

@section('title', 'スケジュール')
    
@section('content')
    <div class="tab_strip">
        <a href="{{ route('schedules.index') }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'schedules.index' ? ' is-active' : '' }}">
            今後の予定
        </a>
        <a href="{{ route('schedules.ended') }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'schedules.ended' ? ' is-active' : '' }}">
            過去の予定
        </a>
    </div>
    <app-container>
        @foreach ($schedules as $month => $group)
            <list-view>
                <template v-slot:title>{{ $month }}</template>
                @foreach ($group as $schedule)
                    <list-view-item href="{{ route('schedules.show', $schedule) }}">
                        <template v-slot:title>
                            {{ $schedule->name }}
                        </template>
                        <template v-slot:meta>
                            @datetime($schedule->start_at)〜 • {{ $schedule->place }}
                        </template>
                        @summary($schedule->description)
                    </list-view-item>
                @endforeach
            </list-view>
        @endforeach
        @empty ($schedules)
            <list-view-empty icon-class="far fa-calendar-alt" text="予定はありません" />
        @endempty
    </app-container>
@endsection
