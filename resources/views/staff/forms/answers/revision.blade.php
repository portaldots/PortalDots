@extends('layouts.app')

@section('title', $form->name . ' — 第' . $revision->revision . '版')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.forms.answers.edit', ['form' => $form, 'answer' => $answer]) }}">
        {{ $form->name }}
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header>
        <template v-slot:title>{{ $form->name }} — 第{{ $revision->revision }}版(閲覧のみ)</template>
        <p class="text-muted">
            提出日時 : @datetime($revision->submitted_at)
            @isset($revision->submittedBy)
                • 提出者 : {{ $revision->submittedBy->name_family }} {{ $revision->submittedBy->name_given }}
            @endisset
        </p>
    </app-header>

    <app-container>
        <list-view>
            @foreach ($questions as $question)
                @include('includes.question', [
                    'is_disabled' => true,
                    'revision' => $revision,
                    'show_upload_route' => 'staff.forms.answers.revisions.uploads.show',
                ])
            @endforeach
        </list-view>
    </app-container>
@endsection
