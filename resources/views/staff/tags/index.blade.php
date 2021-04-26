@extends('layouts.app')

@section('title', '企画タグ管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    <staff-grid
        api-url="{{ route('staff.tags.api') }}"
        v-bind:key-translations="{
            id: 'タグID',
            name: 'タグ',
            created_at: '作成日時',
            updated_at: '更新日時',
        }"
    >
        <template v-slot:toolbar>
            <a
                class="btn is-primary"
                href="{{ route('staff.tags.create') }}"
            >
                <i class="fas fa-plus fa-fw"></i>
                新規タグ
            </a>
            <a
                class="btn is-primary-inverse is-no-shadow is-no-border"
                href="{{ route('staff.tags.export') }}"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{-- 新しいタブで開かないと、他のボタンが disabled になってしまう --}}
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力(タグ別企画一覧)
            </a>
        </template>
        <template v-slot:activities="{ row }">
            <form-with-confirm
                v-bind:action="`{{ route('staff.tags.destroy', ['tag' => '%%TAG%%']) }}`.replace('%%TAG%%', row['id'])" method="post"
                v-bind:confirm-message="`タグ「${row['name']}」を削除しますか？

• 企画に「${row['name']}」タグが紐付けられている場合、紐付け解除されます。企画自体は削除されません
• 「お知らせを閲覧可能なユーザー」から「${row['name']}」タグの指定が解除されます。「${row['name']}」タグしか指定されていないお知らせは、【全ユーザーが閲覧可能】となります
• 申請フォームの「回答可能な企画タグ」から「${row['name']}」タグの指定が解除されます。「${row['name']}」タグしか指定されていないフォームは、【企画に所属している全ユーザーが回答可能】となります`"
            >
                @method('delete')
                @csrf
                <a v-bind:href="`{{ route('staff.tags.edit', ['tag' => '%%TAG%%']) }}`.replace('%%TAG%%', row['id'])" title="編集" class="btn is-primary is-no-shadow">
                    <i class="fas fa-pencil-alt fa-fw"></i>
                </a>
                <button type="submit" title="削除" class="btn is-danger is-no-shadow">
                    <i class="fas fa-trash fa-fw"></i>
                </button>
            </form-with-confirm>
        </template>
        <template v-slot:td="{ row, keyName }">
            @{{ row[keyName] }}
        </template>
    </staff-grid>
@endsection
