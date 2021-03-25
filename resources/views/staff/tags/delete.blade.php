@extends('layouts.app')

@section('title', '企画タグの削除')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.tags.index') }}">
        企画タグ管理
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>
                「{{ $tag->name }}」タグの削除
            </template>

            <list-view-card>
                <ul>
                    <li>企画に「{{ $tag->name }}」タグが紐付けられている場合、紐付け解除されます。企画自体は削除されません</li>
                    <li>「お知らせを閲覧可能なユーザー」から「{{ $tag->name }}」タグの指定が解除されます。「{{ $tag->name }}」タグ以外に「お知らせを閲覧可能なユーザー」が設定されていないお知らせは、<strong>全ユーザーが閲覧可能となります</strong></li>
                    <li>申請フォームの「回答可能な企画のタグ」から「{{ $tag->name }}」タグの指定が解除されます。「{{ $tag->name }}」タグ以外に「回答可能な企画のタグ」が設定されていないフォームは、<strong>企画に所属している全ユーザーが回答可能となります</strong></li>
                </ul>
            </list-view-card>
            <form-with-confirm action="{{ route('staff.tags.destroy', ['tag' => $tag]) }}" method="post"
                    confirm-message="本当に「{{ $tag->name }}」タグを削除しますか？">
                @method('delete')
                @csrf
                <list-view-action-btn danger button submit>
                    削除する
                </list-view-action-btn>
            </form-with-confirm>
        </list-view>
    </app-container>
@endsection
