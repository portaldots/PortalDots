@component('mail::message')
# お問い合わせを承りました

@isset($circle)
{{ $circle->name }} 様
@else
{{ $sender->name }} 様
@endisset

以下の内容でお問い合わせを承りました。

## お問い合わせをされた方
本お問い合わせに対する返答は、以下の方へ行います。<br />
（場合によっては、責任者・学園祭係(副責任者)の双方へ返答を行うことがあります）

- 名前 : {{ $sender->name }}
- 学籍番号 : {{ $sender->student_id }}

## お問い合わせの内容
<pre>{{ $contactBody }}</pre>
@endcomponent
