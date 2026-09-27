@if ($question->type === 'heading')
    <question-heading name="{{ $question->name }}">
        <div data-turbolinks="false" class="markdown">
            @markdown($question->description)
        </div>
    </question-heading>
@elseif ($question->type === 'table')
    @php
        $table_columns = is_array($question->table) ? $question->table : [];
        $table_value = old('answers.' . $question->id, $answer_details[$question->id] ?? []);
        if (!is_array($table_value)) {
            $table_value = [];
        }
        $table_errors = [];
        $error_prefix = 'answers.' . $question->id . '.';
        foreach ($errors->getMessages() as $key => $messages) {
            if (str_starts_with($key, $error_prefix)) {
                $table_errors[substr($key, strlen($error_prefix))] = $messages[0];
            }
        }

        if (!empty($answer)) {
            $table_envelope = app(\App\Services\Forms\AnswerDetailsService::class)
                ->getTableAnswerEnvelopeByAnswer($answer, $question->id);
            $current_column_ids = array_column($table_columns, 'id');
            foreach (($table_envelope['columns'] ?? []) as $snapshot_column) {
                if (!in_array($snapshot_column['id'] ?? null, $current_column_ids, true)) {
                    $snapshot_column['deleted'] = true;
                    $table_columns[] = $snapshot_column;
                    if (is_array($table_value)) {
                        foreach ($table_value as $row_id => &$row_value) {
                            if (is_array($row_value) && array_key_exists($snapshot_column['id'], $table_envelope['rows'][$row_id] ?? [])) {
                                $row_value[$snapshot_column['id']] = $table_envelope['rows'][$row_id][$snapshot_column['id']];
                            }
                        }
                        unset($row_value);
                    }
                }
            }
        }

        $table_upload_route = ($show_upload_route ?? 'forms.answers.uploads.show') === 'staff.forms.answers.uploads.show'
            ? 'staff.forms.answers.uploads.table.show'
            : 'forms.answers.uploads.table.show';
        $table_upload_url_template = !empty($answer)
            ? route($table_upload_route, ['form' => $form, 'answer' => $answer, 'question' => $question, 'row' => '__ROW__', 'column' => '__COLUMN__'])
            : null;
    @endphp
    <question-item @if ($question->is_required) required @endif
        type="table" v-bind:question-id="{{ $question->id }}" name="{{ $question->name }}"
        description="{{ $question->description }}" v-bind:value="{{ json_encode($table_value) }}"
        v-bind:table-columns="{{ json_encode($table_columns) }}"
        v-bind:table-errors="{{ json_encode($table_errors) }}"
        v-bind:table-upload-url-template="{{ json_encode($table_upload_url_template) }}"
        v-bind:number-min="{{ $question->number_min ?? 'null' }}"
        v-bind:number-max="{{ $question->number_max ?? 'null' }}"
        v-bind:disabled="{{ json_encode($is_disabled ?? false) }}"
        @error('answers.' . $question->id)
        invalid="{{ $message }}"
        @enderror></question-item>
@elseif ($question->type === 'textarea' && !empty($is_disabled) && $is_disabled)
    {{-- 複数行入力されたテキストをスクロールすることなく全文表示できるよう、 --}}
    {{-- textareaタグではなくpreタグで回答内容を表示 --}}
    <list-view-form-group>
        <template v-slot:label>{{ $question->name }}</template>
        <template v-slot:description>{{ $question->description }}</template>
        <pre style="white-space: pre-wrap;">{{ $answer_details[$question->id] ?? '' }}</pre>
    </list-view-form-group>
@else
    {{-- 【v-bind:question-id の値について】 --}}
    {{-- Vue に String ではなく Number 型であると認識させるため --}}
    {{-- v-bind を利用 --}}

    {{-- 【v-bind:value の値について】 --}}
    {{-- ファイルアップロード済の場合は、アップロードしたファイルにアクセスできるURLをvalueに設定 --}}
    <question-item @if ($question->is_required) required @endif
        @if ($question->type === 'upload' && !empty($answer) && !empty($answer_details[$question->id]))
            value="{{ route($show_upload_route ?? 'forms.answers.uploads.show', ['form' => $form, 'answer' => $answer, 'question' => $question]) }}"
        @else
            v-bind:value="{{ json_encode(old('answers.' . $question->id, $answer_details[$question->id] ?? null)) }}"
        @endif
        type="{{ $question->type }}" v-bind:question-id="{{ $question->id }}" name="{{ $question->name }}"
        description="{{ $question->description }}" v-bind:options="{{ json_encode($question->optionsArray) }}"
        v-bind:number-min="{{ $question->number_min ?? 'null' }}"
        v-bind:number-max="{{ $question->number_max ?? 'null' }}"
        v-bind:allowed-types="{{ json_encode($question->allowed_types_array) }}"
        v-bind:disabled="{{ json_encode($is_disabled ?? false) }}"
        @if ($question->type === 'upload' && empty($question->allowed_types)) invalid="この設問は、スタッフによる設定不備があるためファイルをアップロードできません。申し訳ございませんが {{ config('portal.admin_name') }} までお問い合わせください。" @endif
        @error('answers.' . $question->id)
        invalid="{{ $message }}"
        @enderror></question-item>
@endif
