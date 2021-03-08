@extends('layouts.app')

@section('title', "{$category->name} — お問い合わせ受付設定")

@section('navbar')
    <app-nav-bar-back inverse href="{{ route('staff.contacts.categories.edit', $category) }}">
        {{ $category->name }}
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>
                項目の削除
            </template>
            <list-view-card class="text-center">
                <p>{{ $category->name }}({{ $category->email }})を削除しますか？</p>

                <form action="{{ route('staff.contacts.categories.destroy', $category) }}" method="post">
                    @method('delete')
                    @csrf
                    <button type="submit" class="btn is-danger">削除</button>
                    <a href="{{ route('staff.contacts.categories.edit', $category) }}" class="btn is-secondary">戻る</a>
                </form>
            </list-view-card>
        </list-view>
    </app-container>
@endsection
