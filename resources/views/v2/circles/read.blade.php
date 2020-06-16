@extends('v2.layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', '企画参加登録')

@section('content')
    <app-header container-medium>
        <template v-slot:title>
            企画参加登録
        </template>
        <p class="text-muted">
            {{ $circle->name }}
        </p>
    </app-header>
    <app-container medium>
        <list-view>
            <list-view-card>
                情報を編集できるのは責任者だけです。以下の情報に誤りがある場合は責任者にご連絡ください。
                <hr>
                @include('v2.includes.circles_info')
            </list-view-card>
        </list-view>
        <div class="text-center pt-spacing-sm pb-spacing">
            <a class="btn is-secondary" href="{{ route('home') }}">
                ホームに戻る
            </a>
        </div>
    </app-container>
@endsection
