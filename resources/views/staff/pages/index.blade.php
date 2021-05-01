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
                href="{{ route('staff.pages.create') }}"
            >
                <i class="fas fa-plus fa-fw"></i>
                新規お知らせ
            </a>
            <a
                class="btn is-primary-inverse is-no-shadow is-no-border"
                href="{{ route('staff.send_emails') }}"
            >
                メール配信設定
            </a>
            <a
                class="btn is-primary-inverse is-no-shadow is-no-border"
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
            <icon-button v-bind:href="`{{ route('staff.pages.edit', ['page' => '%%PAGE%%']) }}`.replace('%%PAGE%%', row['id'])" title="編集">
                <i class="fas fa-pencil-alt fa-fw"></i>
            </icon-button>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'created_by' || keyName === 'updated_by'">
                {{-- 作成者・更新者 --}}
                @{{ row[keyName].name_family  }} @{{ row[keyName].name_given  }} (@{{ row[keyName].student_id  }} • ID : @{{ row[keyName].id }})
            </template>
            <template v-else-if="keyName === 'viewableTags'">
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
