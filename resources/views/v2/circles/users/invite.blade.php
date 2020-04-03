@extends('v2.layouts.no_drawer')

@section('title', '企画参加登録')

@section('content')
    <app-header container-medium>
        <template v-slot:title>企画参加登録</template>
    </app-header>

    <app-container medium>
        <list-view>
            <template v-slot:title>招待の確認</template>
            <list-view-card>
                あなたは「{{ $circle->name }}」の学園祭係(副責任者)として招待されました。この招待を受け入れますか？
            </list-view-card>
        </list-view>

        <form action="{{ route('circles.users.store', ['circle' => $circle]) }}" method="post">
            @csrf
            <input type="hidden" name="invitation_token" value="{{ $circle->invitation_token }}">
            <div class="text-center pt-spacing-sm pb-spacing">
                <button type="submit" class="btn is-primary">
                    受け入れる
                </button>
                <a class="btn is-secondary" href="{{ route('home') }}">
                    拒否する
                </a>
            </div>
        </form>
    </app-container>
@endsection
