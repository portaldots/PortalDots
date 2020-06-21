@extends('layouts.no_drawer')

@section('title', '企画参加登録')

@section('content')
    @include('includes.circle_register_header')

    <app-container medium>
        <list-view>
            <template v-slot:title>参加登録の提出</template>
            <list-view-card>
                以下の情報で参加登録を提出します。<strong>参加登録の提出後は、登録内容の変更ができなくなります。</strong>
                <hr>
                @include('includes.circle_info')
            </list-view-card>
        </list-view>

        <form action="{{ route('circles.submit', ['circle' => $circle]) }}" method="post">
            @csrf
            <div class="text-center pt-spacing-sm pb-spacing">
                <a class="btn is-secondary" href="{{ route('circles.users.index', ['circle' => $circle]) }}">
                    <i class="fas fa-chevron-left"></i>
                    「メンバーを招待」へもどる
                </a>
                <button type="submit" class="btn is-primary">
                    <strong>参加登録を提出</strong>
                </button>
            </div>
        </form>
    </app-container>
@endsection
