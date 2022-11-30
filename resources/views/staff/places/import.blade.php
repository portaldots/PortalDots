@extends('layouts.app')

@section('title', '場所情報管理 インポート')

@section('top_alert_props', 'container-fluid')

@section('content')
    <app-container>
        <list-view>
            <template v-slot:title>CSVインポート</template>
            <list-view-card>
                場所情報を記入したCSVファイルをアップロードすることで、新規登録や更新が行えます。<br />
                <h4>記入方法と注意</h4>
                <ul>
                    <li>1行目は削除しないでください。</li>
                    <li><b>ID:</b> 新規作成の場合は記入不要です。</li>
                    <li><b>場所名:</b> 場所名を記入してください。重複不可。</li>
                    <li><b>タイプ:</b> 「屋内」, 「屋外」, 「特殊場所」のいずれかを記入してください。</li>
                    <li><b>スタッフ用メモ:</b> スタッフ内でのメモにご利用ください。</li>
                    {{-- " で囲む などの注意を書く？ --}}
                </ul>
                <h4>記入例 ①</h4>
                「ブース10」という屋外ブースを新規作成する場合
                <pre>場所ID, 場所名, タイプ, メモ<br>  , ブース10, 2, 火気使用可能</pre>
            </list-view-card>
            <list-view-action-btn href="{{ route('staff.places.import.template') }}">
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
                <input class="form-control" type="file" accept=".csv" name="file" />
            </list-view-form-group>
        </list-view>
        <div class="text-center pt-spacing-md pb-spacing">
            <button type="submit" class="btn is-primary is-wide">
                送信
            </button>
        </div>
    </app-container>
@endsection
