@extends('layouts.app')

@section('title', '場所情報管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    <staff-grid
        api-url="{{ route('staff.places.api') }}"
        v-bind:key-translations="{
            id: '場所ID',
            name: '場所名',
            type: 'タイプ',
            'type.1': '屋内',
            'type.2': '屋外',
            'type.3': '特殊場所',
            notes: 'スタッフ用メモ',
            created_at: '作成日時',
            updated_at: '更新日時',
        }"
    >
        <template v-slot:toolbar>
            <a
                class="btn is-primary"
                href="{{ route('staff.places.create') }}"
            >
                <i class="fas fa-plus fa-fw"></i>
                新規場所
            </a>
            <a
                class="btn is-primary-inverse is-no-border"
                href="{{ route('staff.places.export') }}"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{-- 新しいタブで開かないと、他のボタンが disabled になってしまう --}}
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力(場所別企画一覧)
            </a>
        </template>
        <template v-slot:activities="{ row }">
            <form-with-confirm
                v-bind:action="`{{ route('staff.places.destroy', ['place' => '%%PLACE%%']) }}`.replace('%%PLACE%%', row['id'])" method="post"
                v-bind:confirm-message="`場所「${row['name']}」を削除しますか？

• 企画の使用場所として「${row['name']}」が設定されている場合、その設定は解除されます。企画自体は削除されません`"
            >
                @method('delete')
                @csrf
                <icon-button v-bind:href="`{{ route('staff.places.edit', ['place' => '%%PLACE%%']) }}`.replace('%%PLACE%%', row['id'])" title="編集">
                    <i class="fas fa-pencil-alt fa-fw"></i>
                </icon-button>
                <icon-button submit title="削除">
                    <i class="fas fa-trash fa-fw"></i>
                </icon-button>
            </form-with-confirm>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'type'">
                {{-- タイプ --}}
                @{{ {
                    1: '屋内',
                    2: '屋外',
                    3: '特殊場所'
                }[row[keyName]] }}
            </template>
            <template v-else>
                @{{ row[keyName] }}
            </template>
        </template>
    </staff-grid>
@endsection
