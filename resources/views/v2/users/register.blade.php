@extends('v2.layouts.no_drawer')

@section('title', 'ユーザー登録')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @method('post')
        @csrf

        <app-container medium>
            <list-view>
                <template v-slot:title>ユーザー登録</template>
                <template v-slot:description>「{{ config('app.name') }}」にユーザー登録します。</template>

                @include('v2.includes.user_register_form')
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">
                    登録
                </button>
            </div>
        </app-container>
    </form>
@endsection
