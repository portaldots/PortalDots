@extends('v2.layouts.app')

@section('title', '申請')

@section('content')
<form method="post" action="">
    @csrf

    <app-container>
        <list-view>
            @foreach ($questions as $question)
                <component
                    is="question-{{ $question->type }}"
                    input-id="question_{{ $question->id }}"
                    input-name="answers[{{ $question->id }}]"
                    name="{{ $question->name }}"
                    description="{{ $question->description }}"
                    {{ $question->required ? 'required' : '' }}
                ></component>
            @endforeach
        </list-view>
    </app-container>

    <app-container class="text-center pt-spacing-md">
        <button type="submit" class="btn is-primary is-wide">送信</button>
    </app-container>
</form>
@endsection
