@component('mail::message')
# {{ $documentName }}

第{{ $version }}版の確認をお願いします。

@component('mail::button', ['url' => $url, 'color' => 'primary'])
確認する
@endcomponent
@endcomponent
