@extends('layouts.no_drawer')

@section('title', 'メールアドレス一覧 - お問い合わせ')

@section('navbar')
    <app-nav-bar-back inverse href="{{ url('/home_staff') }}" data-turbolinks="false">
        戻る
    </app-nav-bar-back>
@endsection

@section('content')
    <app-header container-medium>
        <template v-slot:title>お問い合わせ受付設定</template>
    </app-header>
    <app-container medium>
        <list-view>
            <list-view-card>
                <p>ここでメールアドレスを設定するとポータルからのお問い合わせを振り分けることができます。</p>
            </list-view-card>
            @foreach ($categories as $category)
                <list-view-item href="{{ route('staff.contacts.categories.edit', ['category' => $category]) }}">
                    <template v-slot:title>
                        {{ $category->name }}
                    </template>
                    {{ $category->email }}
                </list-view-item>
            @endforeach
            <list-view-action-btn href="{{ route('staff.contacts.categories.create') }}" icon-class="fas fa-plus">
                メールアドレスを追加
            </list-view-action-btn>
        </list-view>
    </app-container>
@endsection
