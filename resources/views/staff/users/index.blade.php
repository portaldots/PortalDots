@extends('layouts.app')

@section('title', 'ユーザー情報管理')

@section('content')
    <div class="tab_strip">
        <a href="{{ route('staff.users.index') }}"
            class="tab_strip-tab{{ Route::currentRouteName() === 'staff.users.index' ? ' is-active' : '' }}">
            登録ユーザー
        </a>
    </div>
    <staff-grid
        api-url="{{ route('staff.users.index') }}"
        csv-export-url="/home_staff/users/export"
    >
        <template v-slot:th="{ keyName }">
            @{{
                {
                    id: 'ユーザーID',
                    student_id: '学籍番号',
                    name_family: '姓',
                    name_family_yomi: '姓(よみ)',
                    name_given: '名',
                    name_given_yomi: '名(よみ)',
                    email: 'メールアドレス',
                    tel: '電話番号',
                    is_staff: 'スタッフ',
                    is_admin: '管理者',
                    email_verified_at: 'メール認証',
                    univemail_verified_at: '本人確認',
                    is_verified_by_staff: '手動認証',
                    signed_up_at: 'ユーザー登録完了日時',
                    notes: 'スタッフ用メモ',
                    created_at: '作成日時',
                    updated_at: '更新日時',
                }[keyName]
            }}
        </template>
        <template v-slot:activities="{ row }">
            <a v-bind:href="`/home_staff/users/edit/${row.id}`" title="編集" class="btn is-secondary" data-turbolinks="false">
                <i class="fas fa-pencil-alt fa-fw"></i>
            </a>
        </template>
        <template v-slot:td="{ row, keyName }">
            @{{ row[keyName] }}
        </tempate>
    </staff-grid>
@endsection
