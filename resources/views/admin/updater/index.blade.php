@extends('layouts.app')

@section('title', 'PortalDots の更新')

@section('content')
    <app-header>
        <template v-slot:title>PortalDots の更新</template>
        <p>利用可能な更新を確認し、PortalDotsを更新します。</p>
    </app-header>

    <app-container>
        <list-view>
            <template v-slot:title>更新前の確認</template>
            <list-view-card>
                <ul>
                    <li>更新中はPortalDotsを利用できません。</li>
                    <li>ブラウザを閉じた場合は、復旧コードを使って更新画面へ戻り、処理を再開してください。</li>
                    <li>更新前に、サーバー外へDBとファイルのバックアップを保存してください。</li>
                    <li>復元できない障害が発生した場合は停止を維持します。画面の案内と外部バックアップを使って復旧してください。</li>
                </ul>
            </list-view-card>

            <list-view-form-group>
                <template v-slot:label>現在の状態</template>
                <p>現在のバージョン: <strong>{{ $updater['current_version'] ?? '確認できません' }}</strong></p>
                @if ($updater['active_job_id'])
                    <p>更新処理が進行中です。</p>
                    <a class="btn is-primary" href="{{ url('/updater.php') }}">更新・復旧画面を開く</a>
                @elseif ($updater['diagnostic'])
                    <div class="alert error mt-spacing-sm">{{ $updater['diagnostic'] }}</div>
                @endif
            </list-view-form-group>

            @if ($updater['enabled'] && !$updater['active_job_id'])
                <list-view-form-group>
                    <template v-slot:label>利用可能な更新を確認</template>
                    <form method="post" action="{{ route('admin.updater.check') }}">
                        @csrf
                        <button type="submit" class="btn is-primary-inverse">更新を確認</button>
                    </form>
                </list-view-form-group>
            @endif

            @isset($release)
                <list-view-form-group>
                    <template v-slot:label>更新版 {{ $release['target_version'] }}</template>
                    <form method="post" action="{{ route('admin.updater.start') }}">
                        @csrf
                        <input type="hidden" name="manifest_digest" value="{{ $manifest_digest }}">
                        <label class="form-checkbox__label">
                            <input class="form-checkbox__input" type="checkbox" required>
                            外部バックアップを取得し、サービス停止と自動復元の制約を確認しました
                        </label>
                        <p><button type="submit" class="btn is-primary">更新を開始</button></p>
                    </form>
                </list-view-form-group>
            @endisset
        </list-view>
    </app-container>
@endsection
