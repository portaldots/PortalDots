@extends('layouts.app')

@section('title', '更新を開始しました')

@section('content')
    <app-header>
        <template v-slot:title>復旧コードを保存してください</template>
        <p>このコードは再表示できません。更新が完了するまで、PortalDotsとは別の安全な場所に保存してください。</p>
    </app-header>

    <app-container>
        <list-view>
            <list-view-form-group>
                <template v-slot:label>復旧コード</template>
                <p><code style="font-size: 1.15rem; word-break: break-all">{{ $job['recovery_code'] }}</code></p>
                <p>更新版: {{ $job['target_version'] }}</p>
            </list-view-form-group>
            <list-view-form-group>
                <template v-slot:label>更新・復旧画面へ進む</template>
                <form method="post" action="{{ url('/updater.php') }}" autocomplete="off">
                    <input type="hidden" name="action" value="authenticate">
                    <input type="hidden" name="recovery_code" value="{{ $job['recovery_code'] }}">
                    <button type="submit" class="btn is-primary">コードを保存して更新を続ける</button>
                </form>
            </list-view-form-group>
        </list-view>
    </app-container>
@endsection
