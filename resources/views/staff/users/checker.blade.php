@extends('layouts.single_column')

@section('title', 'ユーザー登録チェッカー - ' . config('app.name'))

@section('main')
<div id="vue-user-checker">
    <div class="card mb-3">
        <div class="card-header">ユーザー登録チェッカー</div>
        <div class="card-body">
            <input
                type="text"
                class="form-control"
                placeholder="学籍番号"
                v-model="student_id"
                v-on:keyup.enter="onPressEnter"
            >
            <small class="form-text text-muted">入力すると自動的に検索が始まります</small>
        </div>
    </div>

    <ul class="list-group" v-if="!is_loading">
        <li
            class="list-group-item d-flex justify-content-between align-items-center"
            v-for="item in list"
            v-bind:key="item.id"
        >
            <div class="d-flex flex-column">
                <small class="text-monospace">@{{ item.student_id }}</small>
                <strong>@{{ item.name_family }} @{{ item.name_given }}</strong>
            </div>
            <div>
                <span
                    v-if="!item.email_verified_at || !item.univemail_verified_at"
                    class="text-danger"
                >
                    メール認証未完了
                </span>
                <span
                    v-else
                    class="text-success"
                >
                    登録済み
                </span>
            </div>
        </li>
    </ul>

    <div class="card card-body text-center text-muted" v-if="is_loading">
        <p class="lead m-0">
            <span class="fas fa-sync fa-spin fa-fw" aria-hidden="true"></span>
        </p>
    </div>

    <div class="card card-body text-center text-danger" v-else-if="list.length === 0 && !is_init">
        <p class="lead m-0">該当なし</p>
    </div>
</div>
@endsection
