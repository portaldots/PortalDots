@extends('layouts.app')

@section('title', '団体管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    <data-grid api-url="{{ route('staff.groups.api') }}" v-bind:key-translations="{
                                id: '団体ID',
                                name: '団体名',
                                name_yomi: '団体名(よみ)',
                                users_count: 'メンバー数',
                                circles_count: '企画数',
                                notes: 'スタッフ用メモ',
                                created_at: '作成日時',
                                updated_at: '更新日時',
                            }">
        <template v-slot:toolbar>
            <a class="btn is-primary" href="{{ route('staff.groups.create') }}">
                <i class="fas fa-plus fa-fw"></i>
                新規団体
            </a>
            <a class="btn is-primary-inverse is-no-border" href="{{ route('staff.groups.export') }}" target="_blank"
                rel="noopener noreferrer">
                {{-- 新しいタブで開かないと、他のボタンが disabled になってしまう --}}
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力
            </a>
        </template>
        <template v-slot:activities="{ row, openEditorByUrl }">
            <icon-button
                v-on:click="() => openEditorByUrl(`{{ route('staff.groups.edit', ['group' => '%%GROUP%%']) }}`.replace('%%GROUP%%', row['id']))"
                title="編集">
                <i class="fas fa-pencil-alt fa-fw"></i>
            </icon-button>
            <icon-button
                v-bind:href="`{{ route('staff.groups.email', ['group' => '%%GROUP%%']) }}`.replace('%%GROUP%%', row['id'])"
                title="メール送信">
                <i class="far fa-envelope fa-fw"></i>
            </icon-button>
            <form-with-confirm
                v-bind:action="`{{ route('staff.groups.destroy', ['group' => '%%GROUP%%']) }}`.replace('%%GROUP%%', row['id'])"
                method="post"
                v-bind:confirm-message="`団体「${row['name']}」を削除しますか？

• 「${row['name']}」に紐づく企画、およびそれらの企画が送信した申請の回答はすべて削除されます。`"
                inline>
                @method('delete')
                @csrf
                <icon-button submit title="削除">
                    <i class="fas fa-trash fa-fw"></i>
                </icon-button>
            </form-with-confirm>
        </template>
        <template v-slot:td="{ row, keyName }">
            @{{ row[keyName] }}
        </template>
    </data-grid>
@endsection
