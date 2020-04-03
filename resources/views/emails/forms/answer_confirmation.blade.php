@component('mail::message')
# {{ $form->name }}
申請「{{ $form->name }}」を承りました。

@component('mail::panel')
- 回答ID : {{ $answer->id }}
- 企画の名前 : {{ $circle->name }}
- 企画団体の名前 : {{ $circle->group_name }}
- 回答者 : {{ $applicant->name }}
- 日時 : @datetime($answer->updated_at)
@endcomponent

@foreach ($questions as $question)
@if ($question->type === 'heading')

-----

## {{ $question->name }}
@isset ($question->description){{ $question->description }}@endisset
@else
### {{ $question->name }}

@if (empty($answer_details[$question->id]))
—{{-- 未回答  --}}
@elseif ($question->type === 'checkbox')
@foreach ($answer_details[$question->id] as $detail)
- {{ $detail }}
@endforeach
@elseif ($question->type === 'upload')
✓アップロード済 — [アップロードしたファイルをダウンロード]({{ route('forms.answers.uploads.show', ['form' => $form, 'answer' => $answer, 'question' => $question]) }})
@else
{!! nl2br(e($answer_details[$question->id])) !!}
@endif
@endif
<br />

@endforeach

<br /><br />

@component('mail::panel')
フォームの受付期間内であれば、回答を変更することもできます。
@component('mail::button', ['url' => route('forms.answers.edit', ['form' => $form, 'answer' => $answer]), 'color' => 'primary'])
回答を変更
@endcomponent
@endcomponent
@endcomponent
