@extends('layouts.app')

@section('title', '場所情報管理 インポート')

@section('navbar')
    <app-nav-bar-back href="{{ route('staff.places.index') }}">
        場所情報管理
    </app-nav-bar-back>
@endsection

@section('content')
    <form action="{{ route('staff.places.import.store') }}" method="post" enctype="multipart/form-data">
        @csrf

        <app-header>
            <template v-slot:title>場所情報 インポート</template>
        </app-header>

        <app-container>
            <list-view>
                <list-view-card>
                    <p>場所情報を記入したCSVファイルをアップロードすると、新規登録または更新できます。</p>
                    <h4>記入方法と注意</h4>
                    <ul>
                        <li>1行目のヘッダーは変更しないでください。</li>
                        <li><b>場所ID:</b> 新規作成する行は空欄にしてください。更新する行は既存の場所IDを入力してください。</li>
                        <li><b>場所名:</b> 重複しない場所名を入力してください。</li>
                        <li><b>タイプ:</b> 「屋内」「屋外」「特殊場所」のいずれかを入力してください。</li>
                        <li><b>スタッフ用メモ:</b> 必要に応じて入力してください。</li>
                        <li>CSVファイルはUTF-8で保存してください。</li>
                        <li>テンプレートでは、数式として解釈される文字や空白で始まる場所名・メモに、保護用の「'」を付けています。取り込み時に保護分だけ取り除きます。</li>
                        <li>場所名・メモを「'」で始めたい場合は、先頭を「''」と入力してください。</li>
                        <li>1行でもエラーがある場合は、すべての行を取り込みません。</li>
                        <li>エラーの行番号は、CSV上で各レコードが始まる行を示します。</li>
                    </ul>
                </list-view-card>
                @can('staff.places.export')
                    <list-view-action-btn href="{{ route('staff.places.import.template') }}" download>
                        <i class="fas fa-download fa-fw"></i>
                        テンプレートをダウンロード
                    </list-view-action-btn>
                @endcan
            </list-view>

            @if (session('importErrors'))
                <list-view>
                    <template v-slot:title>CSVのエラー</template>
                    <list-view-card>
                        <table class="places-import-errors">
                            <thead>
                                <tr>
                                    <th>開始行</th>
                                    <th>項目</th>
                                    <th>理由</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (session('importErrors') as $error)
                                    <tr>
                                        <td>{{ $error['line'] }}</td>
                                        <td>{{ $error['attribute'] }}</td>
                                        <td>{{ $error['reason'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </list-view-card>
                </list-view>
            @endif

            <list-view>
                <template v-slot:title>アップロード</template>
                <list-view-form-group label-for="importFile">
                    <template v-slot:label>
                        CSVファイル
                        <app-badge danger>必須</app-badge>
                    </template>
                    <template v-slot:description>アップロードするCSVファイルを選択してください。</template>
                    <input id="importFile" class="form-control @error('importFile') is-invalid @enderror" type="file"
                        accept=".csv,text/csv" name="importFile" required>
                    @if ($errors->has('importFile'))
                        <template v-slot:invalid>
                            @foreach ($errors->get('importFile') as $message)
                                {{ $message }}
                            @endforeach
                        </template>
                    @endif
                </list-view-form-group>
            </list-view>

            <div class="text-center pt-spacing-md pb-spacing">
                <button type="submit" class="btn is-primary is-wide">インポート</button>
            </div>
        </app-container>
    </form>

@endsection

@push('css')
    <style>
        .places-import-errors {
            width: 100%;
            border-collapse: collapse;
        }

        .places-import-errors th,
        .places-import-errors td {
            padding: 0.5rem;
            text-align: left;
            vertical-align: top;
        }
    </style>
@endpush
