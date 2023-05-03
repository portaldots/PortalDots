@component('mail::message')
# 企画参加登録を提出しました
{{ $circle->group_name }} 様

以下の内容で企画参加登録を提出しました

{{ config('portal.admin_name') }}より指示がある場合は従ってください。
また、内容確認のためご連絡を差し上げる場合がございます。
@component('mail::panel')
- 企画名 : {{ $circle->name }}
- 企画名(よみ) : {{ $circle->name_yomi }}
- 出店を企画する団体の名称 : {{ $circle->group_name }}
- 出店を企画する団体の名称(よみ) : {{ $circle->group_name_yomi }}
- メンバー
@foreach ($circle->users as $user)
@if ($user->pivot->is_leader === true)
  - {{ $user->name }}({{ $user->student_id }}) (責任者)
@else
  - {{ $user->name }}({{ $user->student_id }})
@endif
@endforeach
@endcomponent

@if (!empty($questions))
@foreach ($questions as $question)
@include('emails.includes.question_email', ['form' => $participation_form])
@endforeach
@endif
@endcomponent
