@extends('layouts.app')

@section('title', 'お知らせ管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    <staff-grid
        api-url="{{ route('staff.pages.api') }}"
        v-bind:key-translations="{
            id: 'お知らせID',
            title: 'タイトル',
            body: '本文',
            is_important: '重要',
            created_at: '作成日時',
            created_by: '作成者',
            updated_at: '更新日時',
            updated_by: '更新者',
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
                href="{{ url('/home_staff/pages/export') }}"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{-- 新しいタブで開かないと、他のボタンが disabled になってしまう --}}
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力
            </a>
        </template>
        <template v-slot:activities="{ row }">
            <a v-bind:href="`{{ route('staff.pages.edit', ['page' => '%%PAGE%%']) }}`.replace('%%PAGE%%', row['id'])" title="編集" class="btn is-primary is-no-shadow">
                <i class="fas fa-pencil-alt fa-fw"></i>
            </a>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'created_by' || keyName === 'updated_by'">
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
