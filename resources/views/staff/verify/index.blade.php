@extends('layouts.no_drawer')

@section('no_circle_selector', true)

@section('title', 'スタッフ認証')

@section('content')
    <form method="post" action="{{ route('staff.verify.index') }}">
        @csrf
        <app-container medium>
            <list-view>
                <template v-slot:title>スタッフ認証</template>
                <template v-slot:description>
                    あなたの<b>連絡先メールアドレス</b>宛に認証メールが送信されました。認証メールに記載されている<strong>認証コード</strong>を入力してください。
                </template>
                <list-view-form-group label-for="verify_code">
                    <template v-slot:label>認証コード</template>
                    <input id="verify_code" type="text" class="form-control @error('verify_code') is-invalid @enderror"
                        name="verify_code" value="{{ old('verify_code') }}" required autofocus>
                    @error('verify_code')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>
            <div class="text-center pt-spacing-md">
                <button type="submit" class="btn is-primary is-wide">ログイン</button>
            </div>
        </app-container>
    </form>
@endsection
