@extends('v2.layouts.app')

@section('title', $form->name . ' — 申請')

@section('navbar')
@if (!empty($answer) && count($answers) > 0 && $form->max_answers > 1)
<a href="{{ route('forms.answers.create', ['form' => $form, 'circle' => $circle]) }}" class="navbar-back">
    <i class="fas fa-chevron-left navbar-back__icon"></i>
    回答の新規作成
</a>
@else
<a href="{{ route('forms.index', ['circle' => $circle]) }}" class="navbar-back">
    <i class="fas fa-chevron-left navbar-back__icon"></i>
    申請
</a>
@endif
@endsection

@section('content')
<form
    method="post"
    action="{{ empty($answer) ? route('forms.answers.store', [$form]) : route('forms.answers.update', [$form, $answer]) }}"
    enctype="multipart/form-data"
>
    @csrf

    @method(empty($answer) ? 'post' : 'patch' )

    <input type="hidden" name="circle_id" value="{{ $circle->id }}">

    <app-header>
        <template v-slot:title>{{ $form->name }}</template>
        <div class="markdown">
            <p class="text-muted">
                受付期間 : @datetime($form->open_at)〜@datetime($form->close_at)
                @if (!$form->isOpen())
                —
                <strong class="text-danger">
                    <i class="fas fa-info-circle"></i>
                    受付期間外です
                </strong>
                @endif
            </p>
            @markdown($form->description)
        </div>
    </app-header>

    <app-container>
        <list-view>
            <list-view-item>
                <template v-slot:title>申請団体名</template>
                {{ $circle->name }}
                @if (count(Auth::user()->circles) > 1)
                {{-- TODO: あとでもうちょっといい感じのコードに書き直す --}}
                —
                <a href="{{ route('forms.answers.create', ['form' => $form]) }}">変更</a>
                @endif
            </list-view-item>
        </list-view>

        {{--
            $answers ← 団体 $circle が回答した全回答（回答新規作成画面で使用。変更画面でも使用可能）
            $answer ← 編集対象の回答（回答変更画面で使用）
        --}}

        @if (empty($answer) && count($answers) > 0)
        <list-view>
            <template v-slot:title>以前の回答を閲覧・変更</template>
            <template v-slot:description>受付期間内に限り、回答の変更ができます</template>
            @foreach ($answers as $_)
            <list-view-item
                href="{{ route('forms.answers.edit', ['form' => $form, 'answer' => $_]) }}"
            >
                <template v-slot:title>
                    @datetime($_->created_at) に新規作成した回答 — 回答ID : {{ $_->id }}
                </template>
                @unless ($_->created_at->eq($_->updated_at))
                <template v-slot:meta>回答の最終更新日時 : @datetime($_->updated_at)</template>
                @endunless
            </list-view-item>
            @endforeach
        </list-view>
        @endif

        <list-view>

            @if (empty($answer) && $form->max_answers > 1)
                <template v-slot:title>回答を新規作成</template>
                @if ($form->max_answers - count($answers) > 0)
                <template v-slot:description>貴団体はこの申請を、あと{{ $form->max_answers - count($answers) }}つ新規作成できます</template>
                @else
                <template v-slot:description>回答数上限({{ $form->max_answers }}つ)に達したため、これ以上新規作成できません。以前の回答の編集は上記より可能です。</template>
                @endif
            @endif
            @isset ($answer)
                <template v-slot:title>{{ $form->isOpen() ? '回答を編集' : '回答を閲覧' }} — 回答ID : {{ $answer->id }}</template>
                <template v-slot:description>回答の最終更新日時 : @datetime($form->updated_at)</template>
            @endisset

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
                        {{-- ファイルアップロード済の場合は、アップロードしたファイルにアクセスできるURLをvalueに設定 --}}
                        value="{{
                            strpos($answer_details[$question->id], 'answer_details') === 0
                                ? route('forms.answers.uploads.show', ['form' => $form, 'answer' => $answer, 'question' => $question])
                                : url('/uploads/applications_form/'. $answer_details[$question->id])
                        }}"
                        @else
                        v-bind:value="{{ json_encode(old('answers.'. $question->id, $answer_details[$question->id] ?? null)) }}"
                        @endif
                        v-bind:options="{{ json_encode($question->optionsArray) }}"
                        v-bind:number-min="{{ $question->number_min ?? 'null' }}"
                        v-bind:number-max="{{ $question->number_max ?? 'null' }}"
                        v-bind:allowed-types="{{ json_encode($question->allowed_types_array) }}"
                        v-bind:disabled="{{ json_encode(!$form->isOpen() || (empty($answer) && $form->max_answers <= count($answers))) }}"
                        @error('answers.'. $question->id)
                        invalid="{{ $message }}"
                        @enderror
                    ></question-item>
                @endif
            @endforeach
        </list-view>

        <div class="text-center pt-spacing-md pb-spacing">
            <button type="submit" class="btn is-primary is-wide"{{ !$form->isOpen() || (empty($answer) && $form->max_answers <= count($answers)) ? ' disabled' : '' }}>送信</button>
            @if (config('app.debug'))
            <button type="submit" class="btn is-primary-inverse" formnovalidate>
                <strong class="badge is-primary">開発モード</strong>
                バリデーションせずに送信
            </button>
            @endif
        </div>
    </app-container>
</form>
@endsection
