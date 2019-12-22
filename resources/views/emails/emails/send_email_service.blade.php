@component('mail::message')
# {{ $subject }}

{!! nl2br($body) !!}
@endcomponent
