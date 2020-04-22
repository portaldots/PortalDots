@extends('errors.layout_no_drawer')

@section('title', 'PortalDots へようこそ！')
@section('top', 'PortalDots へようこそ！')
@section('message')
    <div style="text-align: left; color: #333;">
        <p>あといくつかの設定をすることで、PortalDots を利用できるようになります。</p>
        <hr>
        <p><strong>学園祭実行委員会で使う場合 :</strong> (PortalDots 開発者向けの説明はこの後に続きます)</p>
        <p>（説明がわかりづらくてスミマセン…！なるべく簡単に PortalDots を使えるよう、できるだけ改善していきます！）</p>
        <ol>
            <li><code>.env.<strong>prod</strong></code>ファイルをコピーし、同じフォルダに<code>.env</code>という名前のファイルを作成します。</li>
            <li><code>.env</code>ファイルを開きます。その際、メモ帳ではなく<a href="https://code.visualstudio.com/" target="_blank"
                    rel="noopener noreferrer">Visual Studio Code</a>のようなソフトを利用するのがオススメです。</li>
            <li>
                以下の説明に従い、ファイルを書き換えて保存します。半角スペースなどの空白文字は使えません。<code>%</code>で囲まれた文字列のみ置き換えてください。
                <ul>
                    <li><code>%APP_NAME%</code> → ウェブシステムの名前を指定しましょう。例 : <code>野田祭ウェブシステム</code></li>
                    <li><code>%APP_KEY%</code> → <code>php artisan key:generate --show</code> というコマンドを実行した後に表示される
                        <code>base64:</code> から始まる文字列を入れてください。</li>
                    <li><code>%APP_URL%</code> → PortalDots のトップページのURLを指定しましょう。サーバーの設定で決めたURLを入力してください。例 :
                        <code>https://system.example.com</code></li>
                    <li>
                        <strong>ウェブシステム管理者に関する設定 :</strong>
                        <ul>
                            <li><code>%PORTAL_ADMIN_NAME%</code> → ウェブシステムを運用する実行委員会の名前を指定しましょう。例 : <code>野田祭実行委員会</code>
                            </li>
                            <li><code>%PORTAL_CONTACT_EMAIL%</code> →
                                学園祭の参加団体からの連絡を受け付けるメールアドレスを指定しましょう。お問い合わせ先として画面上に表示されます。例 : <code>system@example.com</code>
                            </li>
                            <li><code>%PORTAL_UNIVEMAIL_DOMAIN%</code> →
                                学校発行メールアドレスの<code>@</code>以降の文字列を入力してください。<code>@</code>より前の文字列が学籍番号ではない場合、現時点ではPortalDotsは利用できません。例
                                : <code>example.ac.jp</code></li>
                            <li><code>%PORTAL_UPLOAD_DIR_CRUD%</code> →
                                通常は空で構いません。<code>%</code>で囲まれた文字列を、<code>%</code>も含めて削除してください。</li>
                        </ul>
                    </li>
                    <li>
                        <strong>データベースに関する設定 :</strong>
                        (パスワード等の詳細はサーバー会社にお問い合わせください。)
                        <ul>
                            <li><code>%DB_HOST%</code> → MySQLデータベースのホスト名を入力してください。</li>
                            <li><code>%DB_PORT%</code> → MySQLデータベースのポート番号を入力してください。</li>
                            <li><code>%DB_DATABASE%</code> → MySQLデータベースのデータベース名を入力してください。</li>
                            <li><code>%DB_USERNAME%</code> → MySQLデータベースのユーザー名を入力してください。</li>
                            <li><code>%DB_PASSWORD%</code> → MySQLデータベースのパスワードを入力してください。</li>
                        </ul>
                    </li>
                    <li>
                        <strong>ウェブシステムからメールを自動送信するための設定 :</strong>
                        (ユーザー登録機能などで必須です。パスワード等の詳細はサーバー会社にお問い合わせください)
                        <ul>
                            <li><code>%MAIL_HOST%</code> → メールサーバーのホスト名を入力してください。</li>
                            <li><code>%MAIL_PORT%</code> → メールサーバーのポート番号を入力してください。</li>
                            <li><code>%MAIL_USERNAME%</code> → メールサーバーのユーザー名を入力してください。</li>
                            <li><code>%MAIL_PASSWORD%</code> → メールサーバーのパスワードを入力してください。</li>
                            <li><code>%MAIL_FROM_ADDRESS%</code> → PortalDots から送信されるメールの差出人メールアドレスを入力しましょう。</li>
                            <li><code>%MAIL_FROM_NAME%</code> → PortalDots
                                から送信されるメールの差出人の名前を入力しましょう。実行委員会名やウェブシステム名と同じにするのがオススメです。</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><code>php artisan migrate</code>コマンドを実行します。エラーが表示される場合、<code>.env</code>ファイルのデータベース設定に間違いがないか、よくご確認ください。
            </li>
            <li>【メール一斉送信機能を利用する場合】サーバーの CRON 設定において、<code>php artisan schedule:run</code>
                というコマンドが5分おきに実行されるように設定してください。サーバーによっては、1時間おきに設定する必要があるかもしれません。</li>
            <li>このページを再読み込みします。エラーが表示された場合、<code>.env</code>ファイルの記載に間違いがないか、よくご確認ください。ログイン画面が表示されたらセットアップ完了です！お疲れ様でした</li>
        </ol>
        <hr>
        <p><strong>PortalDots開発者向け :</strong></p>
        <p>（PortalDots 開発用 Docker サーバーを利用している前提の説明です）</p>
        <ol>
            <li><code>.env.<strong>example</strong></code>ファイルをコピーし、同じフォルダに<code>.env</code>という名前のファイルを作成します。</li>
            <li><code>php artisan key:generate</code> コマンドを実行します。</li>
            <li><code>composer migrate</code>コマンドを実行します。</li>
            <li>このページを再読み込みします。エラーが表示された場合、<code>.env</code>ファイルの記載に間違いがないか、よくご確認ください。ログイン画面が表示されたらセットアップ完了です！お疲れ様でした</li>
        </ol>
    </div>
@endsection
