@extends('v2.layouts.app')

@section('title', '申請')
    
@section('content')
    @if(empty($circle))
        <header class="header">
            <app-container>
                <h1 class="header__title">
                    団体参加登録が未完了です
                </h1>
            </app-container>
        </header>
        <app-container>
            <p>団体参加登録が済んでいないため、申請を行うことができません</p>
            <p>詳細については「{{ config('portal.admin_name') }}」までお問い合わせください</p>
            <p>※ すでに団体参加登録を行った場合でも反映に時間がかかることがあります</p>
            <p><a href="{{ route('home') }}" class="btn is-primary is-block">ホームに戻る</a></p>
        </app-container>
    @else
        <div class="tab_strip">
            <a href="{{ route('forms.index', ['circle' => $circle]) }}"
                class="tab_strip-tab{{ Route::currentRouteName() === 'forms.index' ? ' is-active' : '' }}">
                受付中
            </a>
            <a href="{{ route('forms.closed', ['circle' => $circle]) }}"
                class="tab_strip-tab{{ Route::currentRouteName() === 'forms.closed' ? ' is-active' : '' }}">
                受付終了
            </a>
            <a href="{{ route('forms.all', ['circle' => $circle]) }}"
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
                        <list-view-item href="{{ route('forms.answers.create', ['form' => $form, 'circle' => $circle]) }}">
                            <template v-slot:title>
                                {{ $form->name }}
                                @if ($form->answered($circle))
                                    <span class="badge is-success">提出済</span>
                                @endif
                                @if ($form->yetOpen())
                                    <span class="badge is-muted">受付開始前</span>
                                @endif
                            </template>
                            <template v-slot:meta>
                                @if ($form->yetOpen())
                                    @datetime($form->open_at) から受付開始
                                @else
                                    @datetime($form->close_at) まで受付
                                @endif
                                @if ($form->max_answers > 1)
                                    • 1団体あたり{{ $form->max_answers }}つ回答可能
                                @endif
                            </template>
                            @summary($form->description)
                        </list-view-item>
                    @endforeach
                </list-view>
            @endif
        </app-container>
    @endif
@endsection
