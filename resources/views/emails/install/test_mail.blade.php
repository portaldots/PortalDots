@component('mail::message')
# PortalDots テストメール

PortalDots のインストールを完了させるには、以下のパスワードをインストール画面に入力してください。

@component('mail::panel')
**{{ $password }}**
@endcomponent
@endcomponent
