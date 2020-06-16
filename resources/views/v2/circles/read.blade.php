@extends('v2.layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', '企画参加登録')

@section('content')
    <app-header container-medium>
        <template v-slot:title>
            企画参加登録
        </template>
        <span class="text-muted">
            {{ $circle->name }}
        </span>
        <form-with-confirm action="{{ route('circles.users.destroy', ['circle' => $circle, 'user' => Auth::user()]) }}"
            method="post" confirm-message="本当にこの企画を抜けますか？">
            @method('delete')
            @csrf
            <button type="submit" class="btn is-danger is-sm" style="display:inline-block;">
                この企画から抜ける
            </button>
        </form-with-confirm>
    </app-header>
    <app-container medium>
        <list-view>
            <list-view-card>
                <strong>企画情報の修正や、企画参加登録を提出することができるのは、企画責任者のみです。</strong>
                <hr>
                @include('v2.includes.circles_info')
            </list-view-card>
        </list-view>
    </app-container>
@endsection
