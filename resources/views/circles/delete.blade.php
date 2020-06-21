@extends('layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', '企画参加登録')

@section('content')
    <app-header container-medium>
        <template v-slot:title>
            企画参加登録
        </template>
    </app-header>

    <app-container medium>
        <list-view>
            <template v-slot:title>参加登録の削除</template>
            <list-view-card class="text-center">
                <p>「{{ $circle->name }}」({{ $circle->group_name }})の企画参加登録を削除します。</p>

                <form-with-confirm action="{{ route('circles.destroy', ['circle' => $circle]) }}" method="post"
                    confirm-message="本当に「{{ $circle->name }}」({{ $circle->group_name }})の企画参加登録を削除しますか？">
                    @method('delete')
                    @csrf
                    <button type="submit" class="btn is-danger">
                        <strong>参加登録を削除</strong>
                    </button>
                    <a href="{{ url()->previous() }}" class="btn is-secondary">
                        削除しない
                    </a>
                </form-with-confirm>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
