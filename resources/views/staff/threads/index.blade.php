@extends('layouts.app')

@section('title', term('contact'))

@section('top_alert_props', 'container-fluid')

@section('content')
    <data-grid api-url="{{ route('staff.threads.api') }}" v-bind:key-translations="{
                id: '会話ID',
                circle_id: '企画',
                'circle_id.id': '企画ID',
                'circle_id.name': '企画名',
                'circle_id.group_name': '企画を出店する団体の名称',
                user_id: '企画に所属していない送信者',
                'user_id.id': 'ユーザーID',
                'user_id.name_family': '姓',
                'user_id.name_given': '名',
                status: '状況',
                'status.needs_staff': '対応が必要',
                'status.awaiting_reply': '返答待ち',
                'status.resolved': '解決済み',
                assignee_id: '担当者',
                'assignee_id.id': '担当者ID',
                'assignee_id.name_family': '担当者の姓',
                'assignee_id.name_given': '担当者の名',
                last_entry_at: '最終やり取り日時',
                created_at: '作成日時',
            }">
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'circle_id'">
                {{-- 企画 --}}
                <template v-if="row[keyName]">
                    <b>@{{ row[keyName].name }}</b> — @{{ row[keyName].group_name }}
                </template>
            </template>
            <template v-else-if="keyName === 'status'">
                {{-- 状況 --}}
                <span class="text-primary" v-if="row[keyName] === 'awaiting_reply'">返答待ち</span>
                <span class="text-muted" v-else-if="row[keyName] === 'resolved'">解決済み</span>
                <span v-else>対応が必要</span>
            </template>
            <template v-else-if="keyName === 'assignee_id'">
                {{-- 担当者 --}}
                @{{ row[keyName] ? row[keyName].name : '未設定' }}
            </template>
            <template v-else-if="keyName === 'user_id'">
                {{-- 企画に所属していない送信者 --}}
                @{{ row[keyName] ? row[keyName].name : '' }}
            </template>
            <template v-else>
                @{{ row[keyName] }}
            </template>
        </template>
        <template v-slot:activities="{ row }">
            <icon-button
                v-bind:href="`{{ route('staff.threads.show', ['thread' => '%%THREAD%%']) }}`.replace('%%THREAD%%', row['id'])"
                title="開く">
                <i class="fas fa-comments fa-fw"></i>
            </icon-button>
        </template>
    </data-grid>
@endsection
