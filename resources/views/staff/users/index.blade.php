@extends('layouts.app')

@section('title', 'ユーザー情報管理')

@section('content')
    <staff-grid
        api-url="{{ route('staff.users.api') }}"
    >
        <template v-slot:toolbar>
            <a
                class="btn is-success is-no-shadow"
                href="/home_staff/users/export"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{-- 新しいタブで開かないと、他のボタンが disabled になってしまう --}}
                <i class="fas fa-file-csv"></i>
                CSVで出力
            </a>
        </template>
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
                    signed_up_at: 'ユーザー登録完了日時',
                    notes: 'スタッフ用メモ',
                    created_at: '作成日時',
                    updated_at: '更新日時',
                }[keyName]
            }}
        </template>
        <template v-slot:activities="{ row }">
            <a v-bind:href="`/home_staff/users/edit/${row.id}`" title="編集" class="btn is-primary is-no-shadow" data-turbolinks="false">
                <i class="fas fa-pencil-alt fa-fw"></i>
            </a>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'email_verified_at'">
                {{-- メール認証 --}}
                <span v-if="row[keyName]" class="text-success">認証済み</span>
                <span v-else class="text-danger">
                    未認証
                </span>
            </template>
            <template v-else-if="keyName === 'univemail_verified_at'">
                {{-- 本人確認 --}}
                <span v-if="row[keyName]" class="text-success">確認済み</span>
                <span v-else class="text-danger">
                    <form-with-confirm
                        v-bind:action="`{{ route('staff.users.verified', ['user' => '%%USER%%']) }}`.replace('%%USER%%', row['id'])" method="post"
                        confirm-message="本人確認は本来、「{{ config('app.name') }}」からユーザー自身が持つ学校発行メールアドレスに届く本人確認メールによって行われます。
しかし、ユーザーが学校発行メールアドレスを利用できない場合、代替手段として「本人確認済としてマーク」できます。
ユーザー本人に学生証を提示してもらう等して、あなたが手動で本人確認を行ってください。

本人確認はできましたか？

※ ユーザーが「{{ config('app.name') }}」に登録している学籍番号を変更した場合、本人確認未完了状態に戻ります"
                    >
                        @method('patch')
                        @csrf
                        未確認
                        -
                        <button type="submit" class="btn is-primary is-sm is-no-shadow">本人確認済としてマーク</button>
                    </form-with-confirm>
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
