@extends('layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', '参加登録を提出しました！')

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>参加登録を提出しました！</template>
            <template v-slot:description>企画ID: {{ $circle->id }}</template>
            <list-view-card>
                <p class="text-center text-success text-xl">
                    <i class="fas fa-check-circle"></i>
                </p>
                <div data-turbolinks="false" class="markdown">
                    @markdown($confirmationMessage)
                </div>
                <p class="pt-spacing text-center">
                    <a href="{{ route('home') }}" class="btn is-primary">
                        ホームへ戻る
                    </a>
                </p>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
