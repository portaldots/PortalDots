<list-view-form-group label-for="student_id">
    <template v-slot:label>{{ config('portal.student_id_name') }}</template>
    <input id="student_id" type="text" class="form-control @error('student_id') is-invalid @enderror" name="student_id"
        value="{{ old('student_id') }}" required autocomplete="username">
    @error('student_id')
        <template v-slot:invalid>{{ $message }}</template>
    @enderror
    <template v-slot:append>
        {{ '@' . config('portal.univemail_domain_part') }}
    </template>
</list-view-form-group>
<list-view-form-group label-for="name">
    <template v-slot:label>名前</template>
    <template v-slot:description>
        姓と名の間にはスペースを入れてください
    </template>
    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name"
        value="{{ old('name') }}" required autocomplete="name">
    @error('name')
        <template v-slot:invalid>{{ $message }}</template>
    @enderror
</list-view-form-group>
<list-view-form-group label-for="name_yomi">
    <template v-slot:label>名前(よみ)</template>
    <template v-slot:description>
        姓と名の間にはスペースを入れてください
    </template>
    <input id="name_yomi" type="text" class="form-control @error('name_yomi') is-invalid @enderror" name="name_yomi"
        value="{{ old('name_yomi') }}" required>
    @error('name_yomi')
        <template v-slot:invalid>{{ $message }}</template>
    @enderror
</list-view-form-group>
<list-view-form-group label-for="email">
    <template v-slot:label>連絡先メールアドレス</template>
    <template v-slot:description>
        連絡先メールアドレスとして{{ config('portal.univemail_name') }}もご利用になれます
    </template>
    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
        value="{{ old('email') }}" required autocomplete="email">
    @error('email')
        <template v-slot:invalid>{{ $message }}</template>
    @enderror
</list-view-form-group>
<list-view-form-group label-for="tel">
    <template v-slot:label>連絡先電話番号</template>
    <input id="tel" type="tel" class="form-control @error('tel') is-invalid @enderror" name="tel"
        value="{{ old('tel') }}" required>
    @error('tel')
        <template v-slot:invalid>{{ $message }}</template>
    @enderror
</list-view-form-group>
<list-view-form-group label-for="password">
    <template v-slot:label>パスワード</template>
    <template v-slot:description>8文字以上で入力してください</template>
    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"
        required autocomplete="new-password">
    @error('password')
        <template v-slot:invalid>{{ $message }}</template>
    @enderror
</list-view-form-group>
<list-view-form-group label-for="password_confirmation">
    <template v-slot:label>パスワード(確認)</template>
    <template v-slot:description>確認のため、パスワードをもう一度入力してください</template>
    <input id="password_confirmation" type="password" class="form-control @error('password') is-invalid @enderror"
        name="password_confirmation" required autocomplete="new-password">
</list-view-form-group>
