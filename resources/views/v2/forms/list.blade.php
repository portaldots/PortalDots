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
        <p><a href="{{ url('/') }}" class="btn is-primary is-block">ホームに戻る</a></p>
    </app-container>
@else
    <div class="tab_strip">
        <a
            href="{{ route('forms.index', ['circle' => $circle]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'forms.index' ? ' is-active' : '' }}"
        >
            受付中
        </a>
        <a
            href="{{ route('forms.closed', ['circle' => $circle]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'forms.closed' ? ' is-active' : '' }}"
        >
            受付終了
        </a>
        <a
            href="{{ route('forms.all', ['circle' => $circle]) }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'forms.all' ? ' is-active' : '' }}"
        >
            全て
        </a>
    </div>
    <app-container>
        <list-view>
            @foreach ($forms as $form)
            {{-- 回答ページが Project v2 になったら data-turbolinks="false" は削除する --}}
            <list-view-item
                href="/applications/{{ $form->id }}/answers/create?circle_id={{ $circle->id }}"
                data-turbolinks="false"
            >
                <template v-slot:title>
                    {{ $form->name }}
                    @if ($form->answered($circle))
                        <small class="badge is-success">提出済</small>
                    @endif
                    @if ($form->yetOpen())
                        <small class="badge is-muted">受付開始前</small>
                    @endif
                </template>
                <template v-slot:meta>
                    @datetime($form->close_at) まで受付
                </template>
                @summary($form->description)
            </list-view-item>
            @endforeach
        </list-view>
    </app-container>
@endif
@endsection
