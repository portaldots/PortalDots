@extends('layouts.app')

@section('title', 'お問い合わせ受付設定')

@section('content')
    <app-container>
        <list-view>
            <list-view-card>
                ここでメールアドレスを設定するとポータルからのお問い合わせを振り分けることができます。
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
