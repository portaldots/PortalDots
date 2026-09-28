@component('mail::message')
# {{ $documentName }}

第{{ $version }}版が追加されたため、確認待ちに戻りました。

@component('mail::button', ['url' => $url, 'color' => 'primary'])
確認する
@endcomponent
@endcomponent
