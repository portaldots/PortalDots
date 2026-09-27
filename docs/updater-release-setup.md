# ブラウザ更新のリリース設定

## 初回設定

この設定は、リポジトリ管理権限を持つ端末から1回だけ行います。秘密鍵の保存先には、Git checkoutの外側にあるバックアップ対象ディレクトリを指定します。

```console
php updater/tools/setup-release-signing.php \
  --repo portaldots/PortalDots \
  --key-dir "$HOME/.portaldots-release-keys"
```

既定動作は読み取り専用です。GitHubのタグruleset、`release-signing` Environment、`release-renewal` Environmentを確認し、不足している保護を表示します。GitHubの設定や鍵ファイルは変更しません。

表示内容を確認後、鍵を生成します。

```console
php updater/tools/setup-release-signing.php \
  --repo portaldots/PortalDots \
  --key-dir "$HOME/.portaldots-release-keys" \
  --generate
```

鍵をGitHubへ登録し、保護設定を作成する場合だけ `--apply` を付けます。

```console
php updater/tools/setup-release-signing.php \
  --repo portaldots/PortalDots \
  --key-dir "$HOME/.portaldots-release-keys" \
  --apply
```

`--apply` は次の順に処理します。

1. `v*` タグの作成・更新・削除を管理者へ制限するrulesetを作成する
2. `release-signing` を `v*` タグだけ、`release-renewal` をGitHubから取得した既定ブランチだけに制限する
3. 保護設定を再取得して確認する
4. root署名鍵を `release-signing` のみに、renewal署名鍵を両Environmentへ標準入力で登録する
5. 2つの公開鍵をRepository Variablesへ登録する

秘密鍵はコマンド引数、標準出力、Git、Release assetへ出力しません。既存secretや値の異なる公開鍵variableがある場合は上書きせず停止します。既存Environmentのrequired reviewerなども変更しません。鍵のバックアップはアクセスを制限し、リポジトリとは別に保管してください。

## リリース操作

日常のリリース操作は次の3段階です。

1. 保護された `vN.N.N` タグを作成してpushする
2. `Build, verify, sign, and upload release assets` workflowの全テスト完了を確認する
3. workflowが作成したDraft Releaseの成果物と結果を確認し、Releaseを公開する

workflowは公開済みReleaseを全ページ取得し、同一メジャーの正式版から、root署名済みの更新metadataとfull ZIPを持つ版を自動選択します。候補全件について更新成功、強制終了後の復元、full ZIPの署名済みSHA-256とサイズを検証した同一成果物だけを署名します。該当metadataが1件も存在しない最初のリリースは、ブラウザ更新元を持たないbootstrap releaseになります。metadataが存在するのに取得、形式、署名、asset対応のいずれかが不正な場合はリリースを停止します。

schema 2のroot metadataは、更新ZIP、full ZIP、更新元、migration、renewal公開鍵を固定します。`Renew updater freshness metadata` workflowはrenewal秘密鍵だけを使い、公開済み各メジャーの最新正式版に対して期限leaseだけを更新します。ZIP、root署名、更新元、migrationは変更できません。旧schema 1 metadataは従来のroot期限内だけruntimeが受理し、自動更新元の候補にはなりません。

## 定期更新の監視

freshness workflowは既定ブランチ上のworkflowだけがschedule実行されます。実装時点ではリポジトリの既定ブランチは `5.x`、この変更のPR baseは `6.x` です。このworkflowが既定ブランチへ入るまではscheduleは動きません。既定ブランチを変更した場合は、`release-renewal` Environmentのbranch policyもセットアップCLIで確認してください。

GitHubはpublic repositoryに60日間活動がない場合、scheduled workflowを自動的に無効化することがあります。自動コミットなどで回避せず、workflow失敗または停止の通知を受けた場合に再有効化して手動実行します。詳細は [GitHub Actionsのscheduleイベント](https://docs.github.com/en/actions/reference/workflows-and-actions/events-that-trigger-workflows#schedule) を参照してください。

root署名鍵はscheduled workflowへ渡しません。このセットアップCLIは初回設定専用です。鍵の変更・喪失時は、既存環境が信頼している情報との互換性を保つための移行または復旧手順が別途必要です。このCLIを再実行して既存鍵を置換しないでください。
