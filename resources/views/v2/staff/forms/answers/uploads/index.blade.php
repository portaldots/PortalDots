@extends('v2.layouts.no_drawer')

@section('title', 'アップロードファイルの一括ダウンロード')

@section('content')
<form method="POST">
    @csrf

    <app-container medium>
        <list-view header-title="アップロードファイルの一括ダウンロード(BETA)">
            <list-view-card>
                <p>フォーム「{{ $form->name }}」にてアップロードされたファイルを ZIP 形式で一括ダウンロードします。</p>
                <p><strong>注意事項 (必ずお読みください) :</strong></p>
                <ul>
                    <li>回答一覧において、ファイル名が<code>answer_details/</code>で始まっているファイルについては、当該部分が<code>answer_details__</code>に置き換えられます。</li>
                    <li>回答一覧をCSVでダウンロードする際は<code>answer_details/</code>のままとなります。Wordの「差し込み文書」機能やInDesignの「データ結合」機能を利用される際は、CSVファイル内の<code>answer_details/</code>部分を<code>answer_details__</code>に手動で置き換えてご利用ください。</li>
                    <li>「差し込み文書」機能や「データ結合」機能を利用する際は、回答一覧CSVファイルを、ZIPフォルダ内のファイルと同階層に配置してください。</li>
                    <li><strong>ダウンロードには時間がかかります。</strong>ダウンロードが完了するまで、この画面は開いたままにしてください。</li>
                    <li><strong>本機能はベータ版です。</strong>ファイル数が多い場合、エラーになることがあります。また、予告なしに仕様変更される可能性があります。</li>
                </ul>
            </list-view-card>
            <list-view-action-btn href="{{ route('staff.forms.answers.uploads.download_zip', ['form' => $form]) }}" data-turbolinks="false">
                ダウンロードする(ZIP) — 一回だけクリックしてください
            </list-view-action-btn>
        </list-view>
    </app-container>
    <app-container class="text-center pt-spacing-md">
        <a href="{{ url('home_staff/applications/read/'. $form->id) }}" class="btn is-primary is-wide" data-turbolinks="false">回答一覧へもどる</a>
    </app-container>
</form>
@endsection
