@extends('layouts.app')

@section('title', empty($user) ? '新規作成 — ユーザー管理' : "{$user->name} — ユーザー管理")

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.users.index') }}">
        ユーザー管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form method="post" action="{{ empty($user) ? route('staff.users.store') : route('staff.users.update', $user) }}"
        enctype="multipart/form-data">
        @method(empty($user) ? 'post' : 'patch')
        @csrf

        <app-header>
            @if (empty($user))
                <template v-slot:title>ユーザーを新規作成</template>
            @endif
            @isset($user)
                <template v-slot:title>ユーザーを編集</template>
                <div>ユーザーID : {{ $user->id }}</div>
            @endisset
        </app-header>

        <app-container>
            <list-view>
                <template v-slot:title>一般設定</template>
                <list-view-student-id-and-univemail-input
                    v-bind:allowed-domain-parts="{{ json_encode(config('portal.univemail_domain_part')) }}"
                    v-bind:allow-arbitrary-local-part="{{ config('portal.univemail_local_part') === 'user_id' ? 'true' : 'false' }}"
                    student-id-input-name="student_id" univemail-local-part-input-name="univemail_local_part"
                    univemail-domain-part-input-name="univemail_domain_part"
                    student-id-label="{{ config('portal.student_id_name') }}"
                    univemail-label="{{ config('portal.univemail_name') }}"
                    default-student-id-value="{{ old('student_id', $user->student_id) }}"
                    default-univemail-local-part-value="{{ old('univemail_local_part', $user->univemail_local_part) }}"
                    default-univemail-domain-part-value="{{ old('univemail_domain_part', $user->univemail_domain_part) }}">
                    @error('student_id')
                        <template v-slot:invalid-student-id>{{ $message }}</template>
                    @enderror
                    @error('univemail')
                        <template v-slot:invalid-univemail>{{ $message }}</template>
                    @enderror
                </list-view-student-id-and-univemail-input>
                <list-view-form-group label-for="name">
                    <template v-slot:label>名前</template>
                    <template v-slot:description>
                        姓と名の間にはスペースを入れてください
                    </template>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                        name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @error('name')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="name_yomi">
                    <template v-slot:label>名前(よみ)</template>
                    <template v-slot:description>
                        姓と名の間にはスペースを入れてください
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
                        連絡先メールアドレスとして{{ config('portal.univemail_name') }}も利用できます
                    </template>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                    @error('email')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="tel">
                    <template v-slot:label>連絡先電話番号</template>
                    <input id="tel" type="tel" class="form-control @error('tel') is-invalid @enderror"
                        name="tel" value="{{ old('tel', $user->tel) }}" required>
                    @error('tel')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
                <list-view-card>
                    <app-info-box primary>
                        この画面ではパスワードの変更はできません。ユーザーがパスワードを忘れた場合、ユーザー自身がパスワードを再設定する必要があります。
                    </app-info-box>
                </list-view-card>
            </list-view>

            <list-view>
                <template v-slot:title>ユーザー種別</template>
                <list-view-form-group>
                    <div class="form-radio">
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="user_type" id="userTypeRadios1"
                                value="normal"
                                {{ old('user_type', !$user->is_staff && !$user->is_admin ? 'normal' : '') === 'normal' ? 'checked' : '' }}
                                {{ (!Auth::user()->is_admin && $user->is_admin) || Auth::id() === $user->id ? 'disabled' : '' }}>
                            <strong>一般ユーザー</strong><br />
                            <span class="text-muted">スタッフモードにアクセスできません。</span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="user_type" id="userTypeRadios2"
                                value="staff"
                                {{ old('user_type', $user->is_staff && !$user->is_admin ? 'staff' : '') === 'staff' ? 'checked' : '' }}
                                {{ (!Auth::user()->is_admin && $user->is_admin) || Auth::id() === $user->id ? 'disabled' : '' }}>
                            <strong>スタッフ</strong><br />
                            <span
                                class="text-muted">スタッフモードにアクセスできます。<strong>管理者が「スタッフの権限設定」で許可した機能のみ利用できます。</strong></span>
                        </label>
                        <label class="form-radio__label">
                            <input class="form-radio__input" type="radio" name="user_type" id="userTypeRadios3"
                                value="admin"
                                {{ old('user_type', $user->is_staff && $user->is_admin ? 'admin' : '') === 'admin' ? 'checked' : '' }}
                                {{ Auth::user()->is_admin && Auth::id() !== $user->id ? '' : 'disabled' }}>
                            <strong>管理者</strong><br />
                            <span
                                class="text-muted">スタッフモードを含む{{ config('app.name') }}の全機能を利用できます。{{ config('app.name') }}のシステム設定を変更することができます。</span>
                        </label>
                    </div>
                    @if ($errors->has('user_type'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('user_type') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
                <list-view-card>
                    @if (Auth::id() === $user->id)
                        <app-info-box primary>
                            自分自身の「ユーザー種別」を変更することはできません。
                        </app-info-box>
                    @elseif (!Auth::user()->is_admin && $user->is_admin)
                        <app-info-box primary>
                            「ユーザー種別」が「管理者」のユーザーを「スタッフ」または「一般ユーザー」に変更するには、あなた自身が「管理者」である必要があります。
                        </app-info-box>
                    @elseif (!Auth::user()->is_admin)
                        <app-info-box primary>
                            「ユーザー種別」を「管理者」に変更するためには、あなた自身が「管理者」である必要があります。
                        </app-info-box>
                    @else
                        <app-info-box danger>
                            <strong>セキュリティのため、管理者権限を割り当てるユーザーの人数は最小限にしてください。</strong>
                        </app-info-box>
                    @endif
                </list-view-card>
            </list-view>

            <list-view>
                <list-view-form-group label-for="notes">
                    <template v-slot:label>スタッフ用メモ</template>
                    <template v-slot:description>ここに入力された内容はスタッフのみ閲覧できます。スタッフ内で共有したい事項を残しておくメモとしてご活用ください。</template>
                    <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="5">{{ old('notes', empty($user) ? '' : $user->notes) }}</textarea>
                    @if ($errors->has('notes'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('notes') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <app-fixed-form-footer>
                <button type="submit" class="btn is-primary is-wide">保存</button>
            </app-fixed-form-footer>
        </app-container>
    </form>
@endsection
