@extends('v2.layouts.app')

@section('title', 'お問い合わせ')

@section('content')
    <form method="post" action="{{ route('contacts.post') }}">
        @csrf

        @if (isset($circle))
            <input type="hidden" name="circle_id" value="{{ $circle->id }}">
        @endif

        <app-container>
            <list-view>
                <template v-slot:title>お問い合わせ</template>
                <template v-slot:description>
                    お問い合わせへの返信は <strong>{{ Auth::user()->email }}</strong> に送信されます。メールアドレスは<a
                        href="{{ route('user.edit') }}">ユーザー設定</a>で変更できます。
                </template>
                @if (isset($circle))
                    <list-view-form-group>
                        <template v-slot:label>企画名</template>
                        <input type="text" readonly value="{{ $circle->name }}({{ $circle->group_name }})" class="form-control">
                        @if (Auth::user()->circles()->approved()->count() > 1)
                            <template v-slot:append>
                                <a href="{{ route('circles.selector.show', ['redirect_to' => Request::path()]) }}">変更</a>
                            </template>
                        @endif
                    </list-view-form-group>
                @else
                    <list-view-form-group label-for="name">
                        <template v-slot:label>名前</template>
                        <input type="text" id="name" readonly value="{{ Auth::user()->name }}({{ Auth::user()->student_id }})"
                            class="form-control is-plaintext">
                    </list-view-form-group>
                @endif
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
