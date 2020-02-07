@extends('v2.layouts.no_drawer')

@section('title', 'パスワードの再設定')

@section('content')
<form method="POST" action="{{ url()->full() }}">
    @csrf

    <list-view container-medium header-title="パスワードの再設定" header-description="新しいパスワードを入力してください">
        <list-view-form-group label-for="new_password">
            <template v-slot:label>新しいパスワード</template>
            <input id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" required autocomplete="new-password">
            @error('new_password')
                <template v-slot:invalid>{{ $message }}</template>
            @enderror
        </list-view-form-group>
        <list-view-form-group label-for="new_password_confirmation">
            <template v-slot:label>新しいパスワード(確認)</template>
            <template v-slot:description>確認のため、パスワードをもう一度入力してください</template>
            <input id="new_password_confirmation" type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" name="new_password_confirmation" required autocomplete="new-password">
        </list-view-form-group>
    </list-view>
    <app-container class="text-center pt-spacing-md">
        <button type="submit" class="btn is-primary is-wide">パスワードを変更</button>
    </app-container>
</form>
@endsection
