@if ($question->type === 'heading')
<hr />

## {{ $question->name }}
@isset ($question->description){{ $question->description }}@endisset
@else

<h3>{{ $question->name }}</h3>

@if (empty($answer_details[$question->id]))
—{{-- 未回答  --}}
@elseif ($question->type === 'checkbox')
@foreach ($answer_details[$question->id] as $detail)
- {{ $detail }}
@endforeach
@elseif ($question->type === 'table')
@foreach (\App\Support\TableAnswerPresenter::cells($question, $answer->details->where('question_id', $question->id)->first()?->answer) as $cell)
<p>{{ $cell['label'] }}:
@if ($cell['type'] === 'upload' && $cell['text'] !== '')
<a href="{{ route('forms.answers.uploads.table.show', ['form' => $form, 'answer' => $answer, 'question' => $question, 'row' => $cell['row'], 'column' => $cell['column']]) }}">{{ $cell['text'] }}</a>
@else
{!! nl2br(e($cell['text'])) !!}
@endif
</p>
@endforeach
@elseif ($question->type === 'upload')
✓アップロード済 — [アップロードしたファイルをダウンロード]({{ route('forms.answers.uploads.show', ['form' => $form, 'answer' => $answer, 'question' => $question]) }})
@else
{!! nl2br(e($answer_details[$question->id])) !!}
@endif
@endif
<br />
