# PortalDots 6 公開前の確認結果

2026年10月28日時点の `6.x`（`465c5261`）に対する公開前監査と、その指摘への対応をまとめる。

## サーバー要件

| 項目 | 要件 | 根拠 |
|---|---|---|
| PHP | 8.3 以上(64bit)。拡張 curl、json、mbstring、pdo_mysql、sodium、zip | `composer.json` の `"php": "^8.3"`、updater の preflight。CI は 8.3／8.4／8.5 |
| MySQL | 8.4 以上を動作保証の対象とする | CI（`testcases.yml`、`release.yml`）は `mysql:8.4`。ブラウザ更新は 8.4 未満を拒否する（`updater/src/DatabaseBackup.php`、`build-release.php` の `minimum_mysql`） |
| Node.js | `^20.19.0 \|\| ^22.13.0 \|\| >=24.0.0`、Yarn 1.x | `package.json` の `engines`。CI は Node 22、Yarn 1.22.22 |

MySQL 8.0 でもアプリのテストは通った（監査時、8.0.32 で 768 件）。ただし 8.0 は保証の対象外とし、ブラウザ更新も使えない。開発環境（Sail）の MySQL も 8.4 に揃えた。

README の配布リンクは `major_version=6` を指す。2026年10月28日時点では v6 の公開リリースがないため、このリンクは HTTP 500 を返す（存在しない major 番号と同じ応答）。v6.0.0 を公開した後に、リンクが ZIP へ転送されることを確認する。

## 監査の指摘と対応

| 指摘 | 対応 |
|---|---|
| README・cron サンプル・開発手順の PHP／MySQL 表記と配布リンクが 5.x のまま | README、`cron/lolipop.sh.sample`、`docker-compose.yml` を上記の要件に合わせた。5 からの手動更新とバックアップの注意を追記した |
| Markdown 内の HTML（`<img onerror>` など）がそのまま出力される | サーバーは `ParseMarkdownService` で HTMLPurifier を通す。フォームエディターのプレビューは `xss` で同等に除去する。テストは `tests/Feature/Services/Utils/ParseMarkdownServiceTest.php` |
| お問い合わせのメール送信が途中で失敗すると、同じトークンで再送信しても未送信の宛先に届かない | 宛先ごとに `NotificationDeliveryService::sendOnce` で送信済みを記録し、再送信では未送信の宛先にだけ送る |
| 通知メールがトランザクション内で送られ、途中で失敗すると業務データだけ巻き戻る | スタッフ返信の通知だけ commit 後に送るよう変更し、同じトークンの再送信で未送信の宛先へ送り直す。フォーム送付・資料確認依頼・差し戻し・資料確認リセットの通知は変更していない（再送信の経路がなく、commit 後送信にすると通知が失われるため） |
| `docs/updater-release-setup.md` に鍵の形式と `--generate` の記載がない | 追記した |
| （ブラウザ確認で発見）スタッフ画面の一覧の行リンクが 404 になる。Laravel 13 からルートのパラメーターが個別に URL エンコードされ、`route(..., '%%THREAD%%')` が `%25%25THREAD%25%25` になり、JavaScript の `.replace('%%THREAD%%', id)` が一致しない | スタッフ画面 10 ファイル 23 か所のプレースホルダーを、エンコードされない `__THREAD__` 形式に変えた。テストは `tests/Feature/Http/Controllers/Staff/Threads/IndexActionTest.php` |

## ブラウザでの確認

2026年10月28日、ローカル（PHP 8.4.8 のビルトインサーバー、MySQL 8.4、Brave と Playwright）で、テスト用データを使って確認した。

| 導線 | 結果 |
|---|---|
| 一般ユーザー・スタッフ・管理者のログイン（スタッフ認証を含む） | 成功 |
| フォームの回答 → 差し戻し → 再提出 → 完了 | 成功 |
| 資料の一覧、最新版と旧版の表示 | 成功 |
| お問い合わせ送信 → スタッフ返信 → 一般ユーザー側の表示 | 成功。スタッフの会話一覧から会話を開くリンクは 404 だった（上表のとおり修正済み） |
| 管理者の更新画面（`/admin/update`）の表示 | 成功。開発版のため「更新できません」と表示される |
| Markdown の `<img onerror>`、`<script>`、`javascript:` リンク | 回答画面、フォームエディター、お知らせのエディターのプレビューのいずれでも実行されない |

## ブラウザ更新（updater）の確認

ローカルで、テスト用の Ed25519 鍵を使い、`release.yml` と同じ手順で 6.0.0（bootstrap）と 6.0.1 の配布物を作った。`tests/Updater/engine-release-cycle.php` で、更新、migration 中の強制終了、復元中の強制終了を含む復元が成功した。standalone の updater テスト 5 本も成功した。

確認できていないこと:

- 実在する v6 タグ間の migration 差分を使う build。手元の checkout に v6 タグがないため、候補付き build は `Cannot inspect migrations from 6.0.0.` で止まる。release workflow は `fetch-depth: 0` でタグを取得するので、本番の build ではこの制約はない
- 管理画面の操作から manifest 取得・ZIP ダウンロードまでの一連の流れ。engine のテストは検証済みの情報と ZIP を直接渡して始まる
- Ubuntu／PHP 8.3 での実行（手元は macOS、PHP 8.4.8、MySQL 8.4.13）

## 残っている課題

- フォーム送付・資料確認依頼・差し戻し・資料確認リセットの通知メールは、送信が途中で失敗すると業務データが巻き戻り、再操作で先行の宛先に重複して届くことがある。直すには、commit 後の確実な配送（outbox やキュー）と再開の設計が必要
- 進捗画面（`Staff\Progress\IndexAction`）は `staff.circles.read` だけで、フォームや資料の確認状態をまとめて表示する。この範囲を見せる仕様かを決める
- migration の down は、学籍番号の NULL 補完や会話の統合を元に戻さない。5 に戻す場合はバックアップから復元する
- 次に migration を含むリリースを作るときは、`updater/migration-contracts.json` に各 migration の事後検証 SQL が必要
- `config/portal.php` のコメントは、登録を無効にするとルートを登録しないと説明しているが、実際はルートを登録したうえで 404 を返す
