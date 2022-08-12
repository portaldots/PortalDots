@if ($question->type === 'heading')
    <question-heading name="{{ $question->name }}">
        <div data-turbolinks="false" class="markdown">
            @markdown($question->description)
        </div>
    </question-heading>
@elseif ($question->type === 'textarea' && !empty($is_disabled) && $is_disabled)
    {{-- 複数行入力されたテキストをスクロールすることなく全文表示できるよう、 --}}
    {{-- textareaタグではなくpreタグで回答内容を表示 --}}
    <list-view-form-group>
        <template v-slot:label>{{ $question->name }}</template>
        <template v-slot:description>{{ $question->description }}</template>
        <pre style="white-space: pre-wrap;">{{ $answer_details[$question->id] }}</pre>
    </list-view-form-group>
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
            value="{{ route($show_upload_route ?? 'forms.answers.uploads.show', ['form' => $form, 'answer' => $answer, 'question' => $question]) }}"
        @else
            v-bind:value="{{ json_encode(old('answers.' . $question->id, $answer_details[$question->id] ?? null)) }}"
        @endif
        type="{{ $question->type }}" v-bind:question-id="{{ $question->id }}"
        name="{{ $question->name }}" description="{{ $question->description }}"
        v-bind:options="{{ json_encode($question->optionsArray) }}"
        @if ($question->type === 'table')
            v-bind:table-questions="{{ json_encode($question->table) }}"
        @endif
        v-bind:number-min="{{ $question->number_min ?? 'null' }}"
        v-bind:number-max="{{ $question->number_max ?? 'null' }}"
        v-bind:allowed-types="{{ json_encode($question->allowed_types_array) }}"
        v-bind:disabled="{{ json_encode($is_disabled ?? false) }}"
        @if ($question->type === 'upload' && empty($question->allowed_types))
        invalid="この設問は、スタッフによる設定不備があるためファイルをアップロードできません。申し訳ございませんが {{ config('portal.admin_name') }} までお問い合わせください。"
        @endif
        @error('answers.'. $question->id)
        invalid="{{ $message }}"
        @enderror
        ></question-item>
@endif
