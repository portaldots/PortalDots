@extends('layouts.app')

@section('title', 'ユーザー情報管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    <data-grid api-url="{{ route('staff.users.api') }}" v-bind:key-translations="{
                    id: 'ユーザーID',
                    student_id: '{{ config('portal.student_id_name') }}',
                    name_family: '姓',
                    name_family_yomi: '姓(よみ)',
                    name_given: '名',
                    name_given_yomi: '名(よみ)',
                    email: '連絡先メールアドレス',
                    univemail: '{{ config('portal.univemail_name') }}',
                    tel: '電話番号',
                    is_staff: 'スタッフ',
                    is_admin: '管理者',
                    email_verified_at: 'メール認証',
                    'email_verified_at.true': '未認証',
                    'email_verified_at.false': '認証済み',
                    univemail_verified_at: '本人確認',
                    'univemail_verified_at.true': '未確認',
                    'univemail_verified_at.false': '確認済み',
                    last_accessed_at: '最終アクセス',
                    notes: 'スタッフ用メモ',
                    created_at: '作成日時',
                    updated_at: '更新日時',
                }">
        <template v-slot:toolbar>
            <a class="btn is-primary-inverse is-no-border" href="{{ route('staff.users.export') }}" download>
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力
            </a>
        </template>
        <template v-slot:activities="{ row, openEditorByUrl }">
            <icon-button button
                v-on:click="() => openEditorByUrl(`{{ route('staff.users.edit', ['user' => '%%USER%%']) }}`.replace('%%USER%%', row['id']))"
                title="編集">
                <i class="fas fa-pencil-alt fa-fw"></i>
            </icon-button>
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
                        v-bind:action="`{{ route('staff.users.verified', ['user' => '%%USER%%']) }}`.replace('%%USER%%', row['id'])"
                        method="post"
                        confirm-message="本人確認は本来、「{{ config('app.name') }}」からユーザー自身が持つ{{ config('portal.univemail_name') }}に届く本人確認メールによって行われます。
しかし、ユーザーが{{ config('portal.univemail_name') }}を利用できない場合、代替手段として「本人確認済としてマーク」できます。
ユーザー本人に学生証を提示してもらう等して、あなたが手動で本人確認を行ってください。

本人確認はできましたか？

※ ユーザーが「{{ config('app.name') }}」に登録している{{ config('portal.student_id_name') }}を変更した場合、本人確認未完了状態に戻ります">
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
    </data-grid>
@endsection
