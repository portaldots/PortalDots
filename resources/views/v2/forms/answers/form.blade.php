@extends('v2.layouts.app')

@section('title', '申請')

@section('content')
<form method="post" action="{{ route('forms.answers.store', [$form]) }}">
    @csrf

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
                —
                <a href="#">変更</a>
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
                        value="仮のバリュー"
                        v-bind:options="{{ json_encode($question->optionsArray) }}"
                        v-bind:number-min="{{ $question->number_min ?? 'null' }}"
                        v-bind:number-max="{{ $question->number_max ?? 'null' }}"
                        v-bind:allowed-types="{{ json_encode($question->allowed_types_array) }}"
                    ></question-item>
                @endif
            @endforeach
        </list-view>
    </app-container>

    <app-container class="text-center pt-spacing-md">
        <button type="submit" class="btn is-primary is-wide">送信</button>
    </app-container>
</form>
@endsection
