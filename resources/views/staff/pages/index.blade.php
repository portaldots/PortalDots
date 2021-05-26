@extends('layouts.app')

@section('title', 'お知らせ管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    <staff-grid
        api-url="{{ route('staff.pages.api') }}"
        v-bind:key-translations="{
            id: 'お知らせID',
            title: 'タイトル',
            viewableTags: '閲覧可能なタグ',
            body: '本文',
            is_pinned: '固定',
            is_public: '公開',
            created_at: '作成日時',
            updated_at: '更新日時',
            notes: 'スタッフ用メモ',
        }"
    >
        <template v-slot:toolbar>
            <a
                class="btn is-primary"
                href="{{ route('staff.pages.create') }}"
            >
                <i class="fas fa-plus fa-fw"></i>
                新規お知らせ
            </a>
            <a
                class="btn is-primary-inverse is-no-border"
                href="{{ route('staff.send_emails') }}"
            >
                メール配信設定
            </a>
            <a
                class="btn is-primary-inverse is-no-border"
                href="{{ route('staff.pages.export') }}"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{-- 新しいタブで開かないと、他のボタンが disabled になってしまう --}}
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力
            </a>
        </template>
        <template v-slot:activities="{ row }">
            <form-with-confirm
                v-bind:action="`{{ route('staff.pages.destroy', ['page' => '%%PAGE%%']) }}`.replace('%%PAGE%%', row['id'])" method="post"
                v-bind:confirm-message="`お知らせ「${row['title']}」を削除しますか？`"
            >
                @method('delete')
                @csrf
                <icon-button v-bind:href="`{{ route('staff.pages.edit', ['page' => '%%PAGE%%']) }}`.replace('%%PAGE%%', row['id'])" title="編集">
                    <i class="fas fa-pencil-alt fa-fw"></i>
                </icon-button>
                <icon-button submit title="削除">
                    <i class="fas fa-trash fa-fw"></i>
                </icon-button>
            </form-with-confirm>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'viewableTags'">
                {{-- 閲覧可能なタグ --}}
                <template v-for="tag in row[keyName]">
                    <app-badge primary strong v-bind:key="tag.id">
                        @{{ tag.name }}
                    </app-badge>&nbsp;
                </template>
                <span class="text-muted" v-if="row[keyName].length === 0">
                    全体に公開
                </span>
            </template>
            <template v-else-if="row[keyName] === true">
                <strong>はい</strong>
            </template>
            <template v-else-if="row[keyName] === false">
                -
            </template>
            <template v-else>
                @{{ row[keyName] }}
            </template>
        </template>
    </staff-grid>
@endsection
