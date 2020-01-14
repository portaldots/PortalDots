@extends('v2.layouts.app')

@section('title', 'スケジュール')

@section('content')
<div class="tab_strip">
    <a
        href="{{ route('schedules.index') }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'schedules.index' ? ' is-active' : '' }}"
    >
        今後の予定
    </a>
    <a
        href="{{ route('schedules.ended') }}"
        class="tab_strip-tab{{ Route::currentRouteName() === 'schedules.ended' ? ' is-active' : '' }}"
    >
        過去の予定
    </a>
</div>
@foreach ($schedules as $month => $group)
<div class="listview container">
    <div class="listview-header">
        {{ $month }}
    </div>
    @foreach ($group as $schedule)
    <a class="listview-item" href="{{ route('schedules.show', $schedule) }}">
        <div class="listview-item__day_calendar">
            @include('v2.includes.day_calendar', ['date' => $schedule->start_at])
        </div>
        <div class="listview-item__body">
            <p class="listview-item__title">
                {{ $schedule->name }}
            </p>
            <p class="listview-item__meta">
                @datetime($schedule->start_at)〜 • {{ $schedule->place }}
            </p>
            <p class="listview-item__summary">
                @summary($schedule->description)
            </p>
        </div>
    </a>
    @endforeach
</div>
@endforeach
@empty ($schedules)
<div class="listview">
    <div class="listview-empty">
        <i class="far fa-calendar-alt listview-empty__icon"></i>
        <p class="listview-empty__text">予定はありません</p>
    </div>
</div>
@endempty
@endsection
