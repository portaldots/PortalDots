@component('mail::message')
# {{ $form->name }}
申請「{{ $form->name }}」を承りました。

@component('mail::panel')
- 申請名 : {{ $form->name }}
- 団体名 : {{ $circle->name }}
- 回答者 : {{ $user->name }}
- 日時 : {{ $answer->updated_at }}
@endcomponent

@foreach ($questions as $question)
@if ($question->type === 'heading')

-----

## {{ $question->name }}
{{ $question->description }}

@else
**{{ $question->name }}**

@if (empty($answer_details[$question->id]))
—{{-- 未回答  --}}
@elseif ($question->type === 'checkbox')
@foreach ($answer_details[$question->id] as $detail)
- {{ $detail }}
@endforeach
@elseif ($question->type === 'upload')
✔︎アップロード済 — [アップロードしたファイルをダウンロード]({{ route('forms.answers.uploads.show', ['form' => $form, 'answer' => $answer, 'question' => $question]) }})
@else
{!! nl2br(e($answer_details[$question->id])) !!}
@endif
@endif
<br />

@endforeach

@endcomponent
