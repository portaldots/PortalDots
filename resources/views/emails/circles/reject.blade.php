@component('mail::message')
{{ $circle->group_name }} 様

「{{ $circle->name }}」の参加登録は受理されませんでした。

# 企画の内容
@component('mail::panel')
- 企画名 : {{ $circle->name }}
- 企画名(よみ) : {{ $circle->name_yomi }}
- 出店を企画する団体の名称 : {{ $circle->group_name }}
- 出店を企画する団体の名称(よみ) : {{ $circle->group_name_yomi}}
- メンバー
@foreach ($circle->users as $user)
@if($user->pivot->is_leader === true)
  - {{ $user->name }} (責任者)
@else
  - {{ $user->name }}
@endif
@endforeach
@endcomponent

@isset($circle->status_reason)
# 不受理となった理由
@component('mail::panel')
@markdown($circle->status_reason)
@endcomponent
@endisset

ご不明な点がございましたら{{ config('portal.admin_name') }}までお問い合わせください。
@endcomponent
