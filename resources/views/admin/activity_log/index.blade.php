@extends('layouts.app')

@section('title', 'アクティビティログ')

@section('top_alert_props', 'container-fluid')

@section('content')
    <app-header container-fluid>
        <template v-slot:title>
            アクティビティログ
            <app-badge muted small>BETA</app-badge>
        </template>
        <p>「アクティビティログ」では、{{ config('app.name') }}内で行われた各種データ操作の履歴を確認できます。</p>
        <ul>
            <li>参加登録を提出していない企画情報や、ユーザー情報(一部)の編集履歴は記録していません。</li>
            <li>「アクティビティログ」機能に関するご意見などありましたら、 PortalDots 開発チームへお寄せください。</li>
        </ul>
    </app-header>
    <staff-grid
        api-url="{{ route('admin.activity_log.api') }}"
        v-bind:key-translations="{
            id: 'ログID',
            log_name: 'ログの対象',
            @foreach (App\GridMakers\ActivityLogGridMaker::getAllLogNames() as $log_name => $translation)
                'log_name.{{ $log_name }}': '{{ $translation }}',
            @endforeach
            description: '種別',
            @foreach (App\GridMakers\ActivityLogGridMaker::getAllDescriptions() as $description => $translation)
                'description.{{ $description }}': '{{ $translation }}',
            @endforeach
            subject_id: 'ログの対象でのID',
            causer_id: '実施者',
            properties: '実施された変更',
            created_at: '実施日時',
        }"
    >
        <template v-slot:td="{ row, keyName }">
            <template v-if="keyName === 'properties'">
                {{-- 実施された変更 --}}
                <div class="markdown" style="width: 800px">
                    <details>
                        <summary>変更内容を表示</summary>
                        <table>
                            <thead>
                                <tr>
                                    <td v-if="row[keyName]['old']" style="width: 50%">変更前</td>
                                    <td style="width: 50%">
                                        <template v-if="row[keyName]['old']">
                                            変更後
                                        </template>
                                        <template v-else>
                                            作成・削除された内容
                                        </template>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td v-if="row[keyName]['old']"><pre><code>@{{ row[keyName]['old'] }}</code></pre></td>
                                    <td><pre><code>@{{ row[keyName]['attributes'] }}</code></pre></td>
                                </tr>
                            </tbody>
                        </table>
                    </details>
                </div>
            </template>
            <template v-else>
                @{{ row[keyName] }}
            </template>
        </template>
    </staff-grid>
@endsection
