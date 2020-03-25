@section('title', isset($user) ? 'ユーザー設定' : 'ユーザー登録')
    
@section('content')
    @isset ($user)
        @include('v2.includes.user_settings_tab_strip')
    @endisset
    <form method="POST" action="{{ isset($user) ? route('user.update') : route('register') }}">
        @method(isset($user) ? 'patch' : 'post')
        @csrf
    
        <app-container @empty($user) medium @endempty>
            <list-view>
                <template v-slot:title>{{ isset($user) ? '一般設定' : 'ユーザー登録' }}</template>
                @empty ($user)
                    <template v-slot:description>「{{ config('app.name') }}」にユーザー登録します。</template>
                @endempty
    
                <list-view-form-group label-for="student_id">
                    <template v-slot:label>学籍番号</template>
                    <template v-slot:description>
                        @if (!empty($circles))
                            企画に所属しているか、参加登録の途中のため修正できません
                        @endif
                    </template>
                    <input id="student_id" type="text" class="form-control @error('student_id') is-invalid @enderror"
                        name="student_id" value="{{ old('student_id', isset($user) ? $user->student_id : '') }}"
                        {{ !empty($circles) ? 'disabled' : '' }} required autocomplete="username">
                    @error('student_id')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="name">
                    <template v-slot:label>名前</template>
                    <template v-slot:description>
                        {{ !empty($circles) ? '企画に所属しているか、参加登録の途中のため修正できません' : '姓と名の間にはスペースを入れてください' }}
                    </template>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        value="{{ old('name', isset($user) ? $user->name : '') }}" {{ !empty($circles) ? 'disabled' : '' }}
                        required autocomplete="name">
    
                    @error('name')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="name_yomi">
                    <template v-slot:label>名前(よみ)</template>
                    <template v-slot:description>
                        {{ !empty($circles) ? '企画に所属しているか、参加登録の途中のため修正できません' : '姓と名の間にはスペースを入れてください' }}
                    </template>
                    <input id="name_yomi" type="text" class="form-control @error('name_yomi') is-invalid @enderror"
                        name="name_yomi" value="{{ old('name_yomi', isset($user) ? $user->name_yomi : '') }}"
                        {{ !empty($circles) ? 'disabled' : '' }} required>
    
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
                        value="{{ old('email', isset($user) ? $user->email : '') }}" required autocomplete="email">
                    @error('email')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="tel">
                    <template v-slot:label>連絡先電話番号</template>
                    <input id="tel" type="tel" class="form-control @error('tel') is-invalid @enderror" name="tel"
                        value="{{ old('tel', isset($user) ? $user->tel : '') }}" required>
                    @error('tel')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
    
                @empty($user)
                    <list-view-form-group label-for="password">
                        <template v-slot:label>パスワード</template>
                        <template v-slot:description>8文字以上で入力してください</template>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="new-password">
                        @error('password')
                        <template v-slot:invalid>{{ $message }}</template>
                        @enderror
                    </list-view-form-group>
                    <list-view-form-group label-for="password_confirmation">
                        <template v-slot:label>パスワード(確認)</template>
                        <template v-slot:description>確認のため、パスワードをもう一度入力してください</template>
                        <input id="password_confirmation" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password_confirmation" required
                            autocomplete="new-password">
                    </list-view-form-group>
                @endempty
            </list-view>
    
            @isset($user)
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
            @endisset
    
            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">
                    {{ isset($user) ? '保存' : '登録' }}
                </button>
            </div>
        </app-container>
    </form>
@endsection
