@component('mail::message')
# 企画参加登録が受理されました
@if ($circle->group->is_individual)
{{ $circle->group->users[0]->name }} 様
@else
{{ $circle->group->name }} 様
@endif

「{{ $circle->name }}」の参加登録が**受理**されました！

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
@endcomponent
