@extends('layouts.app')

@section('title', '場所情報管理 インポート')

@section('top_alert_props', 'container-fluid')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.places.index') }}">
        場所情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form action="{{ route('staff.places.import.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <app-header>
            <template v-slot:title>場所情報 インポート</template>
        </app-header>

        <app-container>
            <list-view>
                <list-view-card>
                    場所情報を記入したCSVファイルをアップロードすることで、新規登録や更新が行えます。<br />
                    <h4>記入方法と注意</h4>
                    <ul>
                        <li>1行目は削除しないでください。</li>
                        <li><b>ID:</b> 新規作成の場合は記入不要です。</li>
                        <li><b>場所名:</b> 場所名を記入してください。(重複不可)</li>
                        <li><b>タイプ:</b> 「屋内」「屋外」「特殊場所」のいずれかを記入してください。</li>
                        <li><b>スタッフ用メモ:</b> スタッフ内でのメモにご利用ください。</li>
                    </ul>
                </list-view-card>
                <list-view-action-btn newTab href="{{ route('staff.places.import.template') }}">
                    <i class="fas fa-file-arrow-down"></i>
                    テンプレートをダウンロード
                </list-view-action-btn>
            </list-view>
            <list-view>
                <template v-slot:title>アップロード</template>
                <list-view-form-group>
                    <template v-slot:label>CSVファイル</template>
                    <template v-slot:description>
                        アップロードするファイルを選択してください。
                    </template>
                    <input class="form-control" type="file" accept=".csv" name="importFile" />
                    @error('importFile')
                        <template v-slot:invalid>{{ $message }}</template>
                    @enderror
                </list-view-form-group>
            </list-view>
            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">
                    送信
                </button>
            </div>
        </app-container>
    </form>
@endsection
