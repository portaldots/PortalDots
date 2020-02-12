@extends('v2.layouts.app')

@section('title', '申請')

@section('content')
<form
    method="post"
    action="{{ empty($answer) ? route('forms.answers.store', [$form]) : route('forms.answers.update', [$form, $answer]) }}"
    enctype="multipart/form-data"
>
    @csrf

    @method(empty($answer) ? 'post' : 'patch' )

    <input type="hidden" name="circle_id" value="{{ $circle->id }}">

    <app-container>
        <app-header>
            <template v-slot:title>{{ $form->name }}</template>
            <div class="markdown">
                <p class="text-muted">
                    受付期間 : @datetime($form->open_at)〜@datetime($form->close_at)
                </p>
                @markdown($form->description)
            </div>
        </app-header>

        <list-view>
            <list-view-item>
                <template v-slot:title>申請団体名</template>
                {{ $circle->name }}
                @if (count(Auth::user()->circles) > 1 && empty($answer))
                {{-- TODO: あとでもうちょっといい感じのコードに書き直す --}}
                —
                <a href="{{ route('forms.answers.create', ['form' => $form]) }}">変更</a>
                @endif
            </list-view-item>
        </list-view>

        <list-view>
            @foreach ($questions as $question)
                @if ($question->type === 'heading')
                    <question-heading
                        name="{{ $question->name }}"
                    >
                        <div class="markdown">
                            @markdown($question->description)
                        </div>
                    </question-heading>
                @else
                    <question-item
                        type="{{ $question->type }}"
                        {{-- Vue に String ではなく Number 型であると認識させるため
                            v-bind を利用 --}}
                        v-bind:question-id="{{ $question->id }}"
                        name="{{ $question->name }}"
                        description="{{ $question->description }}"
                        {{ $question->is_required ? 'required' : '' }}
                        @if ($question->type === 'upload' && !empty($answer) && !empty($answer_details[$question->id]))
                        {{-- ファイルアップロード済の場合は、アップロードしたファイルにアクセスできる有効期限付きのURLをvalueに設定 --}}
                        value="{{ URL::temporarySignedRoute('forms.answers.uploads.show', now()->addMinutes(10), ['form' => $form, 'answer' => $answer, 'question' => $question]) }}"
                        @else
                        v-bind:value="{{ json_encode(old('answers.'. $question->id, $answer_details[$question->id] ?? null)) }}"
                        @endif
                        v-bind:options="{{ json_encode($question->optionsArray) }}"
                        v-bind:number-min="{{ $question->number_min ?? 'null' }}"
                        v-bind:number-max="{{ $question->number_max ?? 'null' }}"
                        v-bind:allowed-types="{{ json_encode($question->allowed_types_array) }}"
                        @error('answers.'. $question->id)
                        invalid="{{ $message }}"
                        @enderror
                    ></question-item>
                @endif
            @endforeach
        </list-view>
    </app-container>

    <app-container class="text-center pt-spacing-md">
        <button type="submit" class="btn is-primary is-wide">送信</button>
        @if (config('app.debug'))
        <button type="submit" class="btn is-primary-inverse" formnovalidate>（開発用）バリデーションせずに送信</button>
        @endif
    </app-container>
</form>
@endsection
