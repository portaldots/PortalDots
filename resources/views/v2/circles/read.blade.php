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
                <strong>企画情報の修正や、企画参加登録を提出することができるのは、企画責任者のみです。</strong>
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
