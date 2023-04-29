@component('mail::message')
# 参加登録が受理されませんでした
@if ($circle->group->is_individual)
{{ $circle->group->users[0]->name_family }} {{ $circle->group->users[0]->name_given }} 様
@else
{{ $circle->group->name }} 様
@endif

「{{ $circle->name }}」の参加登録は受理されませんでした。

## 企画の内容
@component('mail::panel')
- 企画名 : {{ $circle->name }}
- 企画名(よみ) : {{ $circle->name_yomi }}
@if ($circle->group->is_individual)
- 責任者: {{ $circle->group->users[0]->name }}({{ $circle->group->users[0]->student_id }})
@else
- 団体名 : {{ $circle->group->name }}
- 団体名(よみ) : {{ $circle->group->name_yomi }}
- メンバー
@foreach ($circle->group->users as $user)
@if($user->pivot->role === 'owner')
  - {{ $user->name }}({{ $user->student_id }}) (責任者)
@else
  - {{ $user->name }}({{ $user->student_id }})
@endif
@endforeach
@endif
@endcomponent

@isset($circle->status_reason)
## 不受理となった理由
@component('mail::panel')
{{ $circle->status_reason }}
@endcomponent
@endisset

ご不明な点がございましたら{{ config('portal.admin_name') }}までお問い合わせください。
@endcomponent
