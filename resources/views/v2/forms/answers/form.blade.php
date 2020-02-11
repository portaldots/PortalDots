@extends('v2.layouts.app')

@section('title', '申請')

@section('content')
<form method="post" action="{{ route('forms.answers.store', [$form]) }}">
    @csrf

    <app-container>
        <list-view>
            @foreach ($questions as $question)
                @if ($question->type === 'heading')
                    <question-heading
                        name="{{ $question->name }}"
                    >
                        @markdown($question->description)
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
