@extends('layouts.app')

@section('title', $form->name . ' — 申請')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.forms.answers.index', ['form' => $form]) }}">
        {{ $form->name }}
    </app-nav-bar-back>
@endsection

@section('content')
    <top-alert type="primary" keep-visible>
        <template v-slot:title>
            <i class="far fa-eye fa-fw" aria-hidden="true"></i>
            プレビュー
        </template>

        このフォームから実際に送信することはできません。
    </top-alert>

    <form method="post" enctype="multipart/form-data">
        @csrf

        @method(empty($answer) ? 'post' : 'patch' )

        <app-header>
            <template v-slot:title>{{ $form->name }}</template>
            <div data-turbolinks="false" class="markdown">
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
                @if (!$form->answerableTags->isEmpty())
                    <p class="text-muted">
                        <app-badge primary outline>限定公開</app-badge>
                        このフォームは、限られた企画のみ回答可能です。
                    </p>
                @endif
                @markdown($form->description)
            </div>
        </app-header>

        <app-container>
            <list-view>
                @foreach ($questions as $question)
                    @include('includes.question', ['show_upload_route' => 'staff.forms.answers.uploads.show'])
                @endforeach
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide" disabled>送信</button>
            </div>
        </app-container>
    </form>
@endsection
