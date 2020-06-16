@component('mail::message')
# お問い合わせ先のメールアドレスとして設定されました

このメールアドレスは「{{ config('app.name') }}」のお問い合わせ先として設定されています。

## 設定の詳細
- 項目名 : {{ $name }}
- メールアドレス : {{ $email }}

@endcomponent
