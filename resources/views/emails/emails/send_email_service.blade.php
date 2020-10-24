@component('mail::message')
# {{ $subject }}

{!! preg_replace("/\r\n|\r|\n/", "  \n", $body) !!}
@endcomponent
