<div class="day_calendar">
    <p class="day_calendar__month">
        {{ $date->format('n') }}
    </p>
    <p class="day_calendar__date">
        {{ $date->format('d') }}
    </p>
    <p class="day_calendar__day">
        @dayByDayId($date->format('w'))
    </p>
</div>
