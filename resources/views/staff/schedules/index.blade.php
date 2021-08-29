@extends('layouts.app')

@section('title', 'スケジュール管理')

@section('top_alert_props', 'container-fluid')

@section('content')
    <staff-grid
        api-url="{{ route('staff.schedules.api') }}"
        v-bind:key-translations="{
            id: '予定ID',
            name: '名前',
            start_at: '開始日時',
            place: '場所',
            description: '説明',
            notes: 'スタッフ用メモ',
            created_at: '作成日時',
            updated_at: '更新日時',
        }"
    >
        <template v-slot:toolbar>
            <a class="btn is-primary" href="{{ route('staff.schedules.create') }}">
                <i class="fas fa-plus fa-fw"></i>
                新規予定
            </a>
            <a class="btn is-primary-inverse is-no-border" href="{{ route('staff.schedules.export') }}" target="_blank"
                rel="noopener noreferrer">
                {{-- 新しいタブで開かないと、他のボタンが disabled になってしまう --}}
                <i class="fas fa-file-csv fa-fw"></i>
                CSVで出力
            </a>
        </template>
        <template v-slot:activities="{ row }">
            <form-with-confirm
                v-bind:action="`{{ route('staff.schedules.destroy', ['schedule' => '%%SCHEDULE%%']) }}`.replace('%%SCHEDULE%%', row['id'])"
                method="post" v-bind:confirm-message="`予定「${row['name']}」を削除しますか？`">
                @method('delete')
                @csrf
                <icon-button
                    v-bind:href="`{{ route('staff.schedules.edit', ['schedule' => '%%SCHEDULE%%']) }}`.replace('%%SCHEDULE%%', row['id'])"
                    title="編集">
                    <i class="fas fa-pencil-alt fa-fw"></i>
                </icon-button>
                <icon-button submit title="削除">
                    <i class="fas fa-trash fa-fw"></i>
                </icon-button>
            </form-with-confirm>
        </template>
        <template v-slot:td="{ row, keyName }">
            @{{ row[keyName] }}
        </template>
    </staff-grid>
@endsection
