@extends('v2.layouts.app')

@section('title', '申請')

@section('content')
    <div class="tab_strip">
        <a href="{{ route('forms.index') }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'forms.index' ? ' is-active' : '' }}">
            受付中
        </a>
        <a href="{{ route('forms.closed') }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'forms.closed' ? ' is-active' : '' }}">
            受付終了
        </a>
        <a href="{{ route('forms.all') }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'forms.all' ? ' is-active' : '' }}">
            全て
        </a>
    </div>
    <app-container>
        @if ($forms->isEmpty())
            <list-view-empty icon-class="far fa-edit" text="このリストは空です" />
        @else
            <list-view>
                @foreach ($forms as $form)
                    <list-view-item href="{{ route('forms.answers.create', ['form' => $form]) }}">
                        <template v-slot:title>
                            {{ $form->name }}
                            @if (isset($circle))
                                @if ($form->answered($circle))
                                    <app-badge success>提出済</app-badge>
                                @endif
                                @if ($form->yetOpen())
                                    <app-badge muted>受付開始前</app-badge>
                                @endif
                            @endif
                        </template>
                        <template v-slot:meta>
                            @if ($form->yetOpen())
                                @datetime($form->open_at) から受付開始
                            @else
                                @datetime($form->close_at) まで受付
                            @endif
                            @if ($form->max_answers > 1)
                                • 1企画あたり{{ $form->max_answers }}つ回答可能
                            @endif
                        </template>
                        @summary($form->description)
                    </list-view-item>
                @endforeach
                @if ($forms->hasPages())
                    <list-view-pagination prev="{{ $forms->previousPageUrl() }}" next="{{ $forms->nextPageUrl() }}" />
                @endif
            </list-view>
        @endif
    </app-container>
@endsection
