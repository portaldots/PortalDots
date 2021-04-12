@extends('layouts.app')

@section('title', empty($user) ? '新規作成 — ユーザー管理' : "{$user->name} — ユーザー管理")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.users.index') }}">
        ユーザー管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($user) ? route('staff.users.store') : route('staff.users.update', $user) }}" enctype="multipart/form-data">
        @method(empty($user) ? 'post' : 'patch' )
        @csrf

        <app-header>
            @if (empty($user))
                <template v-slot:title>ユーザーを新規作成</template>
            @endif
            @isset ($user)
                <template v-slot:title>ユーザーを編集</template>
                <div>ユーザーID : {{ $user->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <list-view-form-group label-for="student_id">
                    <template v-slot:label>学籍番号</template>
                    <input id="student_id" type="text" class="form-control @error('student_id') is-invalid @enderror"
                        name="student_id" value="{{ old('student_id', $user->student_id) }}"
                        required autocomplete="username">
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
                        姓と名の間にはスペースを入れてください
                    </template>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                        value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @error('name')
                    <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="name_yomi">
                    <template v-slot:label>名前(よみ)</template>
                    <template v-slot:description>
                        と名の間にはスペースを入れてください
                    </template>
                    <input id="name_yomi" type="text" class="form-control @error('name_yomi') is-invalid @enderror"
                        name="name_yomi" value="{{ old('name_yomi', $user->name_yomi) }}" required>
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
                <list-view-card>
                    <i class="fas fa-exclamation-circle"></i>
                    この画面ではパスワードの変更はできません。ユーザーがパスワードを忘れた場合、ユーザー自身がパスワードを再設定する必要があります。
                </list-view-card>
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </div>
        </app-container>
    </form>
@endsection
