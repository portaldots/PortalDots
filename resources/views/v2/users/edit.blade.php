@extends('v2.layouts.app')

@section('title', 'ユーザー設定')

@section('content')
    @include('v2.includes.user_settings_tab_strip')
    <form method="POST" action="{{ route('user.update') }}">
        @method('patch')
        @csrf

        <app-container>
            <list-view>
                <template v-slot:title>一般設定</template>

                <list-view-form-group label-for="student_id">
                    <template v-slot:label>学籍番号</template>
                    <template v-slot:description>
                        @if (!$circles->isEmpty())
                            企画に所属しているため修正できません
                        @endif
                    </template>
                    <input id="student_id" type="text" class="form-control @error('student_id') is-invalid @enderror"
                        name="student_id" value="{{ old('student_id', $user->student_id) }}"
                        {{ !$circles->isEmpty() ? 'readonly' : '' }} required autocomplete="username">
                    @error('student_id')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                    <template v-slot:append>
                        {{ '@' . config('portal.univemail_domain') }}
                    </template>
                </list-view-form-group>
                <list-view-form-group label-for="name">
                    <template v-slot:label>名前</template>
                    <template v-slot:description>
                        {{ !$circles->isEmpty() ? '企画に所属しているため修正できません' : '姓と名の間にはスペースを入れてください' }}
                    </template>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        value="{{ old('name', $user->name) }}" {{ !$circles->isEmpty() ? 'readonly' : '' }} required
                        autocomplete="name">
                    @error('name')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="name_yomi">
                    <template v-slot:label>名前(よみ)</template>
                    <template v-slot:description>
                        {{ !$circles->isEmpty() ? '企画に所属しているため修正できません' : '姓と名の間にはスペースを入れてください' }}
                    </template>
                    <input id="name_yomi" type="text" class="form-control @error('name_yomi') is-invalid @enderror"
                        name="name_yomi" value="{{ old('name_yomi', $user->name_yomi) }}"
                        {{ !$circles->isEmpty() ? 'readonly' : '' }} required>
                    @error('name_yomi')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="email">
                    <template v-slot:label>連絡先メールアドレス</template>
                    <template v-slot:description>
                        連絡先メールアドレスとして学校発行のメールアドレスもご利用になれます
                    </template>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                        value="{{ old('email', $user->email) }}" required autocomplete="email">
                    @error('email')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="tel">
                    <template v-slot:label>連絡先電話番号</template>
                    <input id="tel" type="tel" class="form-control @error('tel') is-invalid @enderror" name="tel"
                        value="{{ old('tel', $user->tel) }}" required>
                    @error('tel')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>

            <list-view>
                <template v-slot:description>変更を保存するには、現在のパスワードを入力してください</template>
                <list-view-form-group label-for="password">
                    <template v-slot:label>現在のパスワード</template>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                        name="password" required autocomplete="current-password">
                    @error('password')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
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
