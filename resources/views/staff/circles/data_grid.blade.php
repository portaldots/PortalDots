@extends('layouts.app')

@section('title', '企画情報管理')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.circles.index') }}">
        企画情報管理
    </app-nav-bar-back>
@endsection

@section('top_alert_props', 'container-fluid')

@section('content')
    @isset($participation_type)
        @include('includes.staff_circles_tab_strip')

        <app-header container-fluid>
            <template v-slot:title>
                {{ $participation_type->name }}
            </template>
            <div>
                {{ $participation_type->description }}
            </div>
        </app-header>
    @endisset

    <data-grid
        api-url="{{ isset($participation_type)
            ? route('staff.circles.participation_types.api', ['participation_type' => $participation_type])
            : route('staff.circles.api') }}"
        v-bind:key-translations="{
            id: '企画ID',
            participation_type_id: '参加種別',
            'participation_type_id.id': '参加種別ID',
            'participation_type_id.name': '参加種別名',
            'participation_type_id.users_count_min': 'メンバー最小人数',
            'participation_type_id.users_count_max': 'メンバー最大人数',
            'participation_type_id.created_at': '作成日時',
            'participation_type_id.updated_at': '更新日時',
            name: '企画名',
            name_yomi: '企画名(よみ)',
            group_name: '企画を出店する団体の名称',
            group_name_yomi: '企画を出店する団体の名称(よみ)',
            places: '使用場所',
            tags: 'タグ',
            @if (isset($participation_type)) @foreach ($participation_type->form->questions as $question)
                    {{ App\GridMakers\CirclesGridMaker::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX . $question->id }}: '{{ $question->name }}',
                @endforeach @endif
            submitted_at: '参加登録提出日時',
            status: '登録受理状況',
            'status.rejected': '不受理',
            'status.approved': '受理',
            'status.NULL': '確認中',
            status_set_at: '登録受理状況設定日時',
            status_set_by: '登録受理状況設定ユーザー',
            'status_set_by.id': 'ユーザーID',
            'status_set_by.student_id': '{{ config('portal.student_id_name') }}',
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
        }">
        <template v-slot:toolbar>
            <a class="btn is-primary"
                href="{{ route('staff.circles.create', ['participation_type' => isset($participation_type) ? $participation_type->id : null]) }}">
                <i class="fas fa-plus fa-fw"></i>
                新規企画
            </a>
            <a class="btn is-primary-inverse is-no-border"
                href="{{ isset($participation_type)
                    ? route('staff.circles.participation_types.export', ['participation_type' => $participation_type])
                    : route('staff.circles.export') }}"
                target="_blank" rel="noopener noreferrer">
                {{-- 新しいタブで開かないと、他のボタンが disabled になってしまう --}}
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力
            </a>
            @isset($participation_type->form)
                <a class="btn is-primary-inverse is-no-border"
                    href="{{ route('staff.forms.answers.uploads.index', ['form' => $participation_type->form]) }}">
                    <i class="far fa-file-archive fa-fw"></i>
                    ファイルを一括ダウンロード
                </a>
            @endisset
        </template>
        <template v-slot:activities="{ row, openEditorByUrl }">
            <form-with-confirm
                v-bind:action="`{{ route('staff.circles.destroy', ['circle' => '%%CIRCLE%%']) }}`.replace('%%CIRCLE%%', row['id'])"
                method="post"
                v-bind:confirm-message="`企画「${row['name']}」を削除しますか？

• 「${row['name']}」が送信した申請の回答はすべて削除されます。`">
                @method('delete')
                @csrf
                <icon-button button
                    v-on:click="() => openEditorByUrl(`{{ route('staff.circles.edit', ['circle' => '%%CIRCLE%%']) }}`.replace('%%CIRCLE%%', row['id']))"
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
            <template v-if="keyName === 'participation_type_id' && row[keyName]">
                {{-- 参加種別 --}}
                @{{ row[keyName].name }}(ID:@{{ row[keyName].id }})
            </template>
            <template v-else-if="keyName === 'places'">
                {{-- 使用場所 --}}
                <template v-for="place in row[keyName]" v-bind:key="place.id">
                    <app-badge primary strong>
                        @{{ place.name }}
                    </app-badge>&nbsp;
                </template>
            </template>
            <template v-else-if="keyName === 'tags'">
                {{-- タグ --}}
                <template v-for="tag in row[keyName]" v-bind:key="tag.id">
                    <app-badge primary strong>
                        @{{ tag.name }}
                    </app-badge>&nbsp;
                </template>
            </template>
            <template
                v-else-if="keyName.includes('{{ App\GridMakers\CirclesGridMaker::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX }}')">
                {{-- カスタムフォームへの回答 --}}
                <template v-if="row[keyName] && row[keyName].file_url">
                    <a v-bind:href="row[keyName].file_url" target="_blank" rel="noopener noreferrer">表示</a>
                </template>
                <template v-else-if="row[keyName] && row[keyName].join">
                    @{{ row[keyName].join(', ') }}
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
                @{{ row[keyName].name_family }} @{{ row[keyName].name_given }}
                (@{{ row[keyName].student_id }} •
                ID : @{{ row[keyName].id }})
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
    </data-grid>
@endsection
