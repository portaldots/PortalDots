@extends('layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', '認証してください')

@section('content')
<app-container medium>
    <form action="{{ route('circles.auth', ['circle' => $circle]) }}" method="POST">
        @csrf
        <list-view>
            <template v-slot:title>認証が必要</template>
            <list-view-card>
                <app-info-box primary>
                    このページへアクセスするにはパスワードを入力してください
                </app-info-box>
            </list-view-card>
            <list-view-form-group label-for="password">
                <template v-slot:label>
                    パスワード
                </template>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <template v-slot:invalid>{{ $message }}</template>
                @enderror
            </list-view-form-group>
        </list-view>
        <div class="text-center pt-spacing-sm">
            <button class="btn is-primary is-wide" type="submit">送信</button>
        </div>
    </form>
</app-container>
@endsection
