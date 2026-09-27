@component('mail::message')
# {{ $formName }}

回答を差し戻しました。

@component('mail::panel')
理由 : {{ $reason }}
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'primary'])
回答ページを見る
@endcomponent
@endcomponent
