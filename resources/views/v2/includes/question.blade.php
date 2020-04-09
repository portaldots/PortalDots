@if ($question->type === 'heading')
    <question-heading name="{{ $question->name }}">
        <div class="markdown">
            @markdown($question->description)
        </div>
    </question-heading>
@else
    {{-- 【v-bind:question-id の値について】 --}}
    {{-- Vue に String ではなく Number 型であると認識させるため --}}
    {{-- v-bind を利用 --}}

    {{-- 【v-bind:value の値について】 --}}
    {{-- ファイルアップロード済の場合は、アップロードしたファイルにアクセスできるURLをvalueに設定 --}}
    <question-item @if ($question->is_required)
            required
        @endif
        @if ($question->type === 'upload' && !empty($answer) &&
            !empty($answer_details[$question->id]))
            value="{{ strpos($answer_details[$question->id], 'answer_details') === 0 ? route($show_upload_route ?? 'forms.answers.uploads.show', ['form' => $form, 'answer' => $answer, 'question' => $question]) : url('/uploads/applications_form/' . $answer_details[$question->id]) }}"
        @else
            v-bind:value="{{ json_encode(old('answers.' . $question->id, $answer_details[$question->id] ?? null)) }}"
        @endif
        type="{{ $question->type }}" v-bind:question-id="{{ $question->id }}"
        name="{{ $question->name }}" description="{{ $question->description }}"
        v-bind:options="{{ json_encode($question->optionsArray) }}"
        v-bind:number-min="{{ $question->number_min ?? 'null' }}"
        v-bind:number-max="{{ $question->number_max ?? 'null' }}"
        v-bind:allowed-types="{{ json_encode($question->allowed_types_array) }}"
        v-bind:disabled="{{ json_encode($is_disabled ?? false) }}"
        @error('answers.'. $question->id)
        invalid="{{ $message }}"
        @enderror
        ></question-item>
@endif
