@extends('layouts.app')

@section('title', '企画情報管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    @if (empty($custom_form))
        <top-alert type="primary" keep-visible container-fluid>
            <template v-slot:title>
                <i class="fa fa-star fa-fw" aria-hidden="true"></i>
                企画参加登録をウェブ化して時短しませんか？
            </template>

            企画参加登録を「{{ config('app.name') }}」上で受け付けることで、参加登録にかかる事務作業を時短することができます。

            <template v-slot:cta>
                <a href="{{ route('staff.circles.custom_form.index') }}"
                    class="btn is-primary-inverse is-no-border is-wide">
                    <strong>もっと詳しく</strong>
                </a>
            </template>
        </top-alert>
    @endif

    <staff-grid
        api-url="{{ route('staff.circles.api') }}"
        v-bind:key-translations="{
            id: '企画ID',
            name: '企画名',
            name_yomi: '企画名(よみ)',
            group_name: '企画を出店する団体の名称',
            group_name_yomi: '企画を出店する団体の名称(よみ)',
            places: '使用場所',
            tags: 'タグ',
            @if (isset($custom_form))
                @foreach($custom_form->questions as $question)
                    {{ App\GridMakers\CirclesGridMaker::CUSTOM_FORM_QUESTIONS_KEY_PREFIX . $question->id }}: '{{ $question->name }}',
                @endforeach
            @endif
            submitted_at: '参加登録提出日時',
            status: '登録受理状況',
            'status.rejected': '不受理',
            'status.approved': '受理',
            'status.NULL': '確認中',
            status_set_at: '登録受理状況設定日時',
            status_set_by: '登録受理状況設定ユーザー',
            'status_set_by.id': 'ユーザーID',
            'status_set_by.student_id': '学籍番号',
            'status_set_by.name_family': '姓',
            'status_set_by.name_family_yomi': '姓(よみ)',
            'status_set_by.name_given': '名',
            'status_set_by.name_given_yomi': '名(よみ)',
            'status_set_by.email': '連絡先メールアドレス',
            'status_set_by.tel': '電話番号',
            'status_set_by.is_staff': 'スタッフ',
            'status_set_by.is_admin': '管理者',
            'status_set_by.email_verified_at': 'メール認証',
            'status_set_by.univemail_verified_at': '本人確認',
            'status_set_by.notes': 'スタッフ用メモ',
            'status_set_by.created_at': '作成日時',
            'status_set_by.updated_at': '更新日時',
            created_at: '作成日時',
            updated_at: '更新日時',
            notes: 'スタッフ用メモ',
        }"
    >
        <template v-slot:toolbar>
            <a class="btn is-primary" href="{{ route('staff.circles.create') }}">
                <i class="fas fa-plus fa-fw"></i>
                新規企画
            </a>
            <a class="btn is-primary-inverse is-no-border" href="{{ route('staff.circles.custom_form.index') }}">
                <i class="fas fa-users-cog fa-fw"></i>
                企画参加登録機能の設定
                @if (empty($custom_form))
                    <app-badge muted outline strong>未設定</app-badge>
                @elseif(!$custom_form->is_public)
                    <app-badge danger>非公開</app-badge>
                @elseif(!$custom_form->isOpen())
                    <app-badge muted>受付期間外</app-badge>
                @else
                    <app-badge primary strong>受付期間内</app-badge>
                @endif
            </a>
            <a class="btn is-primary-inverse is-no-border" href="{{ route('staff.circles.export') }}" target="_blank"
                rel="noopener noreferrer">
                {{-- 新しいタブで開かないと、他のボタンが disabled になってしまう --}}
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力
            </a>
            @isset($custom_form)
                <a class="btn is-primary-inverse is-no-border"
                    href="{{ route('staff.forms.answers.uploads.index', ['form' => $custom_form]) }}">
                    <i class="far fa-file-archive fa-fw"></i>
                    ファイルを一括ダウンロード
                </a>
            @endisset
        </template>
        <template v-slot:activities="{ row }">
            <form-with-confirm
                v-bind:action="`{{ route('staff.circles.destroy', ['circle' => '%%CIRCLE%%']) }}`.replace('%%CIRCLE%%', row['id'])"
                method="post" v-bind:confirm-message="`企画「${row['name']}」を削除しますか？

• 「${row['name']}」が送信した申請の回答はすべて削除されます。`">
                @method('delete')
                @csrf
                <icon-button
                    v-bind:href="`{{ route('staff.circles.edit', ['circle' => '%%CIRCLE%%']) }}`.replace('%%CIRCLE%%', row['id'])"
                    title="編集">
                    <i class="fas fa-pencil-alt fa-fw"></i>
                </icon-button>
                <icon-button
                    v-bind:href="`{{ route('staff.circles.email', ['circle' => '%%CIRCLE%%']) }}`.replace('%%CIRCLE%%', row['id'])"
                    title="メール送信">
                    <i class="far fa-envelope fa-fw"></i>
                </icon-button>
                <icon-button submit title="削除">
                    <i class="fas fa-trash fa-fw"></i>
                </icon-button>
            </form-with-confirm>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'places'">
                {{-- 使用場所 --}}
                <template v-for="place in row[keyName]">
                    <app-badge primary strong v-bind:key="place.id">
                        @{{ place . name }}
                    </app-badge>&nbsp;
                </template>
            </template>
            <template v-else-if="keyName === 'tags'">
                {{-- タグ --}}
                <template v-for="tag in row[keyName]">
                    <app-badge primary strong v-bind:key="tag.id">
                        @{{ tag . name }}
                    </app-badge>&nbsp;
                </template>
            </template>
            <template
                v-else-if="keyName.includes('{{ App\GridMakers\CirclesGridMaker::CUSTOM_FORM_QUESTIONS_KEY_PREFIX }}')">
                {{-- カスタムフォームへの回答 --}}
                <template v-if="row[keyName] && row[keyName].file_url">
                    <a v-bind:href="row[keyName].file_url" target="_blank" rel="noopener noreferrer">表示</a>
                </template>
                <template v-else-if="row[keyName] && row[keyName].join">
                    @{{ row[keyName] . join(', ') }}
                </template>
            </template>
            <template v-else-if="keyName === 'status'">
                {{-- 登録受理状況 --}}
                <span class="text-danger" v-if="row[keyName] === 'rejected'">不受理</span>
                <span class="text-success" v-else-if="row[keyName] === 'approved'">受理</span>
                <span class="text-muted" v-else>確認中</span>
            </template>
            <template v-else-if="keyName === 'status_set_by' && row[keyName]">
                {{-- 登録受理状況設定ユーザー --}}
                @{{ row[keyName] . name_family }} @{{ row[keyName] . name_given }}
                (@{{ row[keyName] . student_id }} •
                ID : @{{ row[keyName] . id }})
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
