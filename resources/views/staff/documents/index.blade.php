@extends('layouts.app')

@section('title', '配布資料管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    <staff-grid
        api-url="{{ route('staff.documents.api') }}"
        v-bind:key-translations="{
            id: '配布資料ID',
            name: '配布資料名',
            path: 'ファイル',
            size: 'サイズ(バイト)',
            extension: 'ファイル形式',
            schedule_id: 'イベント',
            'schedule_id.id': '予定ID',
            'schedule_id.name': '予定名',
            'schedule_id.start_at': '開始日時',
            'schedule_id.place': '場所',
            'schedule_id.notes': 'スタッフ用メモ',
            'schedule_id.created_at': '作成日時',
            'schedule_id.updated_at': '更新日時',
            description: '説明',
            is_public: '公開',
            is_important: '重要',
            created_at: '作成日時',
            created_by: '作成者',
            'created_by.id': 'ユーザーID',
            'created_by.student_id': '学籍番号',
            'created_by.name_family': '姓',
            'created_by.name_family_yomi': '姓(よみ)',
            'created_by.name_given': '名',
            'created_by.name_given_yomi': '名(よみ)',
            'created_by.email': '連絡先メールアドレス',
            'created_by.tel': '電話番号',
            'created_by.is_staff': 'スタッフ',
            'created_by.is_admin': '管理者',
            'created_by.email_verified_at': 'メール認証',
            'created_by.univemail_verified_at': '本人確認',
            'created_by.notes': 'スタッフ用メモ',
            'created_by.created_at': '作成日時',
            'created_by.updated_at': '更新日時',
            updated_at: '更新日時',
            updated_by: '更新者',
            'updated_by.id': 'ユーザーID',
            'updated_by.student_id': '学籍番号',
            'updated_by.name_family': '姓',
            'updated_by.name_family_yomi': '姓(よみ)',
            'updated_by.name_given': '名',
            'updated_by.name_given_yomi': '名(よみ)',
            'updated_by.email': '連絡先メールアドレス',
            'updated_by.tel': '電話番号',
            'updated_by.is_staff': 'スタッフ',
            'updated_by.is_admin': '管理者',
            'updated_by.email_verified_at': 'メール認証',
            'updated_by.univemail_verified_at': '本人確認',
            'updated_by.notes': 'スタッフ用メモ',
            'updated_by.created_at': '作成日時',
            'updated_by.updated_at': '更新日時',
            notes: 'スタッフ用メモ',
        }"
    >
        <template v-slot:toolbar>
            <a
                class="btn is-primary"
                href="{{ route('staff.documents.create') }}"
            >
                <i class="fas fa-plus fa-fw"></i>
                新規配布資料
            </a>
            <a
                class="btn is-primary-inverse is-no-border"
                href="{{ route('staff.documents.export') }}"
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
                v-bind:action="`{{ route('staff.documents.destroy', ['document' => '%%DOCUMENT%%']) }}`.replace('%%DOCUMENT%%', row['id'])"
                method="post"
                v-bind:confirm-message="`配布資料「${row['name']}」を削除しますか？`"
            >
                @method('delete')
                @csrf
                <icon-button v-bind:href="`{{ route('staff.documents.edit', ['document' => '%%DOCUMENT%%']) }}`.replace('%%DOCUMENT%%', row['id'])" title="編集">
                    <i class="fas fa-pencil-alt fa-fw"></i>
                </icon-button>
                <icon-button submit title="削除">
                    <i class="fas fa-trash fa-fw"></i>
                </icon-button>
            </form-with-confirm>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'path'">
                {{-- ファイル --}}
                <a
                    v-bind:href="`{{ route('staff.documents.show', ['document' => '%%DOCUMENT%%']) }}`.replace('%%DOCUMENT%%', row['id'])"
                    target="_blank"
                    rel="noopener noreferrer"
                >表示</a>
            </template>
            <template v-else-if="keyName === 'schedule_id' && row[keyName]">
                {{-- イベント --}}
                @{{ row[keyName].name  }} (ID : @{{ row[keyName].id }})
            </template>
            <template v-else-if="keyName === 'created_by' || keyName === 'updated_by'">
                {{-- 作成者・更新者 --}}
                @{{ row[keyName].name_family  }} @{{ row[keyName].name_given  }} (@{{ row[keyName].student_id  }} • ID : @{{ row[keyName].id }})
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
