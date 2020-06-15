@extends('v2.layouts.app')

@section('title', 'お問い合わせ')

@section('content')
    <form method="post" action="{{ route('contacts.post') }}">
        @csrf

        <app-container>
            <list-view>
                <template v-slot:title>お問い合わせ</template>
                <list-view-form-group label-for="recipient">
                    <template v-slot:label>宛先</template>
                    <input type="text" id="recipient" readonly value="{{ config('portal.admin_name') }}"
                        class="form-control is-plaintext">
                </list-view-form-group>
                <list-view-form-group label-for="name">
                    <template v-slot:label>名前</template>
                    <input type="text" id="name" readonly value="{{ Auth::user()->name }}"
                        class="form-control is-plaintext">
                </list-view-form-group>
                <list-view-form-group label-for="student_id">
                    <template v-slot:label>学籍番号</template>
                    <input type="text" id="student_id" readonly value="{{ Auth::user()->student_id }}"
                        class="form-control is-plaintext">
                </list-view-form-group>
                <list-view-form-group label-for="email">
                    <template v-slot:label>メールアドレス</template>
                    <template v-slot:description>
                        メールアドレスの変更は<a href="{{ route('user.edit') }}">ユーザー設定</a>で行えます。
                    </template>
                    <input type="email" id="email" readonly value="{{ Auth::user()->email }}"
                        class="form-control is-plaintext">
                </list-view-form-group>
                @unless ($circles->isEmpty() || count($circles) < 1) <list-view-form-group label-for="circle_id">
                        <template v-slot:label>企画の名前</template>
                        <template v-slot:description>どの企画としてお問い合わせするのか選択してください。</template>
                        <select name="circle_id" id="circle_id" class="form-control">
                            @foreach ($circles as $circle)
                                @if (!empty(old('circle_id')) && old('circle_id') === $circle->id)
                                    <option value="{{ $circle->id }}" selected>{{ $circle->name }}</option>
                                @else
                                    <option value="{{ $circle->id }}">{{ $circle->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        </list-view-form-group>
                    @endunless
                    @unless($categories->isEmpty())
                        <list-view-form-group label-for="category">
                            <template v-slot:label>お問い合わせ項目</template>
                            <template v-slot:description>以下のリストから項目を選択してください</template>
                            <select id="category" name="category" class="form-control @error('category') is-invalid @enderror ">
                                <option hidden>選択してください</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category', 0) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                                <option value="0">その他</option>
                            </select>
                            @error('category')
                            <template v-slot:invalid>
                                {{ $message }}
                            </template>
                            @enderror
                        </list-view-form-group>
                    @else
                        <input type="hidden" id="category" name="category" value="0">
                    @endunless
                    <list-view-form-group label-for="contact_body">
                        <template v-slot:label>お問い合わせ内容</template>
                        <template v-slot:description>確認のため、お問い合わせ内容をメールで送信いたします。</template>
                        <textarea name="contact_body" id="contact_body"
                            class="form-control {{ $errors->has('contact_body') ? 'is-invalid' : '' }}" rows="10"
                            required>{{ old('contact_body') }}</textarea>

                        @if ($errors->has('contact_body'))
                            <template v-slot:invalid>
                                @foreach ($errors->get('contact_body') as $message)
                                    {{ $message }}<br>
                                @endforeach
                            </template>
                        @endif
                    </list-view-form-group>
            </list-view>
            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">送信</button>
            </div>
        </app-container>
    </form>
@endsection
