@extends('layouts.no_drawer')

@section('title', 'アップロードファイルの一括ダウンロード')

@section('navbar')
    <app-nav-bar-back inverse href="{{ url('home_staff/applications/read/' . $form->id) }}" data-turbolinks="false">
        {{ $form->name }}
    </app-nav-bar-back>
@endsection

@section('content')
    <app-container medium>
        <list-view>
            <template v-slot:title>
                アップロードファイルの一括ダウンロード
                <app-badge muted small>BETA</app-badge>
            </template>

            <list-view-card>
                <p>フォーム「{{ $form->name }}」にてアップロードされたファイルを ZIP 形式で一括ダウンロードします。</p>
                <p><strong>注意事項 (必ずお読みください) :</strong></p>
                <ul>
                    <li>「差し込み文書」機能や「データ結合」機能を利用する際は、回答一覧CSVファイルを、ZIPフォルダ内のファイルと同階層に配置してください。</li>
                    <li><strong>ダウンロードには時間がかかります。</strong>ダウンロードが完了するまで、この画面は開いたままにしてください。</li>
                    <li><strong>本機能はベータ版です。</strong>ファイル数が多い場合、エラーになることがあります。また、予告なしに仕様変更される可能性があります。</li>
                </ul>
            </list-view-card>
            <form action="{{ route('staff.forms.answers.uploads.download_zip', ['form' => $form]) }}" method="post">
                @csrf
                <list-view-action-btn button submit>
                    ダウンロードする(ZIP)
                </list-view-action-btn>
            </form>
        </list-view>
    </app-container>
@endsection
