@extends('layouts.app')

@section('title', '申請管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    <staff-grid
        api-url="{{ route('staff.forms.api') }}"
        v-bind:key-translations="{
            id: 'フォームID',
            name: 'フォーム名',
            is_public: '公開',
            answerableTags: '回答可能なタグ',
            description: 'フォームの説明',
            open_at: '受付開始日時',
            close_at: '受付終了日時',
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
        }"
    >
        <template v-slot:toolbar>
            <a
                class="btn is-primary"
                href="{{ route('staff.forms.create') }}"
            >
                <i class="fas fa-plus fa-fw"></i>
                新規フォーム
            </a>
            <a
                class="btn is-primary-inverse is-no-shadow is-no-border"
                href="{{ route('staff.forms.export') }}"
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
                v-bind:action="`{{ route('staff.forms.copy', ['form' => '%%FORM%%']) }}`.replace('%%FORM%%', row['id'])" method="post"
                v-bind:confirm-message="`フォーム「${row['name']}」を複製しますか？

• 設問は全て複製されます
• 「${row['name']}のコピー」という名前のフォームが作成されます
• 「${row['name']}のコピー」は非公開です。後から必要に応じて設定を変更してください`"
            >
                @csrf
                <icon-button v-bind:href="`{{ route('staff.forms.edit', ['form' => '%%FORM%%']) }}`.replace('%%FORM%%', row['id'])" title="編集">
                    <i class="fas fa-pencil-alt fa-fw"></i>
                </icon-button>
                <icon-button v-bind:href="`{{ route('staff.forms.editor', ['form' => '%%FORM%%']) }}`.replace('%%FORM%%', row['id'])" title="フォームエディター" data-turbolinks="false">
                    <i class="far fa-edit fa-fw"></i>
                </icon-button>
                <icon-button v-bind:href="`{{ route('staff.forms.answers.index', ['form' => '%%FORM%%']) }}`.replace('%%FORM%%', row['id'])" title="回答一覧">
                    <i class="far fa-eye fa-fw"></i>
                </icon-button>
                <icon-button submit title="複製">
                    <i class="far fa-copy fa-fw"></i>
                </icon-button>
            </form-with-confirm>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'created_by'">
                {{-- 作成者 --}}
                @{{ row[keyName].name_family  }} @{{ row[keyName].name_given  }} (@{{ row[keyName].student_id  }} • ID : @{{ row[keyName].id }})
            </template>
            <template v-else-if="keyName === 'answerableTags'">
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
