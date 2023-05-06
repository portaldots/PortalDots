@component('mail::message')
# {{ $form->name }}
@if ($isEditedByStaff)
スタッフによって申請「{{ $form->name }}」が作成・編集されましたのでお知らせします。
@else
申請「{{ $form->name }}」を承りました。
@endif

{!! preg_replace("/\r\n|\r|\n/", "  \n", $form->confirmation_message ?? "") !!}

@component('mail::panel')
- 回答ID : {{ $answer->id }}
- 企画名 : {{ $circle->name }}
- 企画を出店する団体の名称 : {{ $circle->group_name }}
- 回答者 : {{ $isEditedByStaff ? 'スタッフ' : $applicant->name }}
- 日時 : @datetime($answer->updated_at)
@endcomponent

@foreach ($questions as $question)
@include('emails.includes.question_email')
@endforeach

<br /><br />

@if ($form->isOpen())
@component('mail::panel')
フォームの受付期間内であれば、回答を変更することもできます。
@component('mail::button', ['url' => route('forms.answers.edit', ['form' => $form, 'answer' => $answer]), 'color' => 'primary'])
回答を変更
@endcomponent
@endcomponent
@endif
@endcomponent
