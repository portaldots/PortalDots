@extends('layouts.app')

@section('title', 'ユーザー設定')

@section('content')
    @include('includes.user_settings_tab_strip')

    <form method="POST" action="{{ route('user.password') }}">
        @csrf

        {{-- ブラウザにおける Autocomplete の UX を向上するため、ユーザー名を隠しフィールドに入れておく。 --}}
        {{-- Chrome DevTools で Warning が出るのを防ぐ目的もある。 --}}
        {{-- なお、type="hidden" では効力がないらしく、Warning は消えない。そのため、hidden 属性でフィールドを非表示にしている --}}
        <input hidden type="text" name="username" autocomplete="username"
            value="{{ Auth::user()->student_id ?? Auth::user()->email }}">

        <app-container>
            <list-view>
                <template v-slot:title>パスワード変更</template>
                <template v-slot:description>
                    <a href="{{ route('password.request') }}">
                        パスワードをお忘れの場合はこちら
                    </a>
                </template>
                <list-view-form-group label-for="password">
                    <template v-slot:label>現在のパスワード</template>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                        name="password" required autocomplete="current-password">
                    @error('password')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="new_password">
                    <template v-slot:label>新しいパスワード</template>
                    <template v-slot:description>8文字以上で入力してください</template>
                    <input id="new_password" type="password"
                        class="form-control @error('new_password') is-invalid @enderror" name="new_password" required
                        autocomplete="new-password">
                    @error('new_password')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="new_password_confirmation">
                    <template v-slot:label>新しいパスワード(確認)</template>
                    <template v-slot:description>確認のため、パスワードをもう一度入力してください</template>
                    <input id="new_password_confirmation" type="password"
                        class="form-control @error('new_password') is-invalid @enderror" name="new_password_confirmation"
                        required autocomplete="new-password">
                </list-view-form-group>
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </div>
        </app-container>
    </form>
@endsection
