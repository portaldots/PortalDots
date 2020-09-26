@extends('layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', '認証してください')

@section('content')
<app-container medium>
    <form action="{{ route('circles.auth', ['circle' => $circle]) }}" method="POST">
        @csrf
        <list-view>
            <template v-slot:title>認証が必要です</template>
            <list-view-card>
                <i class="fas fa-exclamation-circle"></i> このページへアクセスするにはパスワードを入力してください
                <hr>
                <div class="form-group">
                    <label for="password" class="form-label @error('password') is-invalid @enderror">パスワード</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="text-danger">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </list-view-card>
        </list-view>
        <div class="text-center pt-spacing-sm">
            <button class="btn is-primary is-wide" type="submit">送信</button>
        </div>
    </form>
</app-container>
@endsection
