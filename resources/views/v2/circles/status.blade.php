@extends('v2.layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', '企画参加登録')

@section('content')
    <app-header container-medium>
        <template v-slot:title>
            企画参加登録
        </template>
        @isset ($circle)
            <p class="text-muted">
                {{ $circle->name }}
            </p>
        @endisset
    </app-header>

    <app-container medium>
        <list-view>
            <list-view-card class="markdown">
                @markdown($circle->status_reason)
            </list-view-card>
        </list-view>
    </app-container>
@endsection
