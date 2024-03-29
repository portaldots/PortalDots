@extends('layouts.app')

@section('title', (empty($category) ? 'メールアドレス追加' : $category->name) . ' — お問い合わせ受付設定')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.contacts.categories.index') }}">
        お問い合わせ受付設定
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header>
        @if (empty($category))
            <template v-slot:title>項目を新規作成</template>
        @endif
        @isset($category)
            <template v-slot:title>項目を編集</template>
            <div class="text-muted">
                項目ID : {{ $category->id }}
                —
                <a href="{{ route('staff.contacts.categories.delete', $category) }}">この項目を削除</a>
            </div>
        @endisset
    </app-header>
    <app-container>
        <form
            action="{{ isset($category) ? route('staff.contacts.categories.update', ['category' => $category]) : route('staff.contacts.categories.create') }}"
            method="post">
            @csrf
            @method(isset($category) ? 'patch' : 'post')
            <list-view>
                <list-view-form-group label-for="name">
                    <template v-slot:label>
                        項目名
                    </template>
                    <template v-slot:description>
                        項目名は「お問い合わせ」内容を選ぶ選択肢としてユーザーに表示されます
                    </template>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $category->name ?? '') }}" required>
                    @error('name')
                        <template v-slot:invalid>
                            {{ $message }}
                        </template>
                    @enderror
                </list-view-form-group>
                <list-view-form-group label-for="email">
                    <template v-slot:label>
                        メールアドレス
                    </template>
                    <template v-slot:description>
                        この項目がお問い合わせ画面で選択された場合、このメールアドレス宛にメールが届きます
                    </template>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $category->email ?? '') }}" required>
                    @error('email')
                        <template v-slot:invalid>
                            {{ $message }}
                        </template>
                    @enderror
                </list-view-form-group>
            </list-view>
            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">保存</button>
                <p class="pt-spacing-md">
                    @empty($category)
                        保存した際に設定したメールアドレスにメールが送信されます。
                    @else
                        メールアドレスを変更した場合、変更後のメールアドレスにメールが送信されます。
                    @endempty
                </p>
            </div>
        </form>
    </app-container>
@endsection
