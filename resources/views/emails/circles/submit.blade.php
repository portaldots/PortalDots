@component('mail::message')
{{ $circle->group_name }} 様

## 以下の内容で企画参加登録を提出しました
@component('mail::panel')
- 企画名 : {{ $circle->name }}
- 企画名(よみ) : {{ $circle->name_yomi }}
- 出店を企画する団体の名称 : {{ $circle->group_name }}
- 出店を企画する団体の名称(よみ) : {{ $circle->group_name_yomi}}
- 企画責任者 : {{ $circle->leader->first()->name }}
@endcomponent


{{ config('portal.admin_name') }}より指示がある場合は従ってください。
また、内容確認のためご連絡を差し上げる場合がございます。
@endcomponent
