@component('mail::message')
# {{ $formName }}

申請「{{ $formName }}」が送付されました。

@component('mail::panel')
期限 : {{ $dueAtText }}
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'primary'])
回答する
@endcomponent
@endcomponent
