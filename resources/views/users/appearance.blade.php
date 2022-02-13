@extends('layouts.app')

@section('title', 'ユーザー設定')

@section('content')
    @include('includes.user_settings_tab_strip')
    <form method="POST" action="{{ route('user.appearance') }}">
        @method('patch')
        @csrf

        <app-container>
            <list-view>
                <template v-slot:title>外観</template>

                <list-view-card>
                    <app-info-box primary>
                        外観設定はお使いのブラウザーに保存されます。Cookieを削除するとこの設定はリセットされます。
                    </app-info-box>
                </list-view-card>
                <list-view-form-group>
                    <div class="form-radio">
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="theme" id="userTypeRadios1" value="system"
                                {{ old('theme', $theme) === 'system' ? 'checked' : '' }}>
                            <strong>自動</strong><br />
                            <span class="text-muted">お使いの端末の設定での外観モード設定に準じます</span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="theme" id="userTypeRadios2" value="light"
                                {{ old('theme', $theme) === 'light' ? 'checked' : '' }}>
                            <strong>ライトテーマ</strong><br />
                            <span class="text-muted">明るい外観になります</span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="theme" id="userTypeRadios3" value="dark"
                                {{ old('theme', $theme) === 'dark' ? 'checked' : '' }}>
                            <strong>ダークテーマ</strong><br />
                            <span class="text-muted">暗い外観になります</span>
                        </label>
                    </div>
                    @if ($errors->has('theme'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('theme') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">
                    保存
                </button>
            </div>
        </app-container>
    </form>
@endsection
