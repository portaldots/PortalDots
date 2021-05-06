@extends('layouts.app')

@section('title', 'スタッフの権限設定')

@section('top_alert_props', 'container-fluid')

@section('content')
    <staff-grid
        api-url="{{ route('staff.permissions.api') }}"
        v-bind:key-translations="{
            id: 'ユーザーID',
            name: '名前',
            student_id: '学籍番号',
            name_family: '姓',
            name_family_yomi: '姓(よみ)',
            name_given: '名',
            name_given_yomi: '名(よみ)',
            is_admin: '管理者',
            permissions: '割り当てられた権限',
        }"
    >
        <template v-slot:activities="{ row }">
            <icon-button v-bind:href="`{{ route('staff.permissions.edit', ['user' => '%%USER%%']) }}`.replace('%%USER%%', row['id'])" v-bind:disabled="row['is_admin']" title="編集">
                <i class="fas fa-pencil-alt fa-fw"></i>
            </icon-button>
        </template>
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'name'">
                @{{ row[keyName] }}<br />
                <span class="text-muted">@{{ row['student_id'] }}</span>
            </template>
            <template v-else-if="keyName === 'permissions' && row['is_admin']">
                <app-badge danger strong>管理者(全機能にアクセス可能)</app-badge>
            </template>
            <template v-else-if="keyName === 'permissions'">
                <template v-for="permission in row[keyName]">
                    <app-badge primary v-bind:key="permission.identifier" v-bind:title="`${permission.display_name} — ${permission.description_html} (${permission.identifier})`">
                        @{{ permission.display_short_name }}
                    </app-badge>
                    @{{" "}}
                </template>
                <strong class="text-danger" v-if="row[keyName].length === 0">
                    利用可能な機能なし
                </strong>
            </template>
            <template v-else>
                @{{ row[keyName] }}
            </template>
        </template>
    </staff-grid>
@endsection
