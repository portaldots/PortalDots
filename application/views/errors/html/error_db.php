<?php
/*
同じHTMLコードが
 app/Exceptions/Handler.php
にも書かれている。このファイルを修正する際は、
 app/Exceptions/Handler.php
に書かれている同様のHTMLコードも修正すること。
*/
?>
<!doctype html>
<meta charset="utf-8">
<title>データベース接続エラー</title>
<div style="text-align: center">
    <h1>データベースと接続できません</h1>
    <hr>
    <p>設定ファイル(.env)内のデータベース設定が正しいかご確認ください。</p>
    <hr>
    <p>Powered by PortalDots</p>
</div>
