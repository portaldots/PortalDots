# コントリビューションガイドライン
PortalDots では、Issue や Pull Request を歓迎します。

## 不具合を発見した場合
[こちらから Issue を作成してください](https://github.com/portal-dots/PortalDots/issues/new?assignees=&labels=bug&template=bug.md&title=%E3%80%90%E7%94%BB%E9%9D%A2%E5%90%8D%E3%80%91+%E4%B8%8D%E5%85%B7%E5%90%88%E3%81%AE%E5%86%85%E5%AE%B9)

## 新機能の提案をしたい場合
[こちらから Issue を作成してください](https://github.com/portal-dots/PortalDots/issues/new?assignees=&labels=enhancement&template=enhancement.md&title=%E3%80%90%E7%94%BB%E9%9D%A2%E5%90%8D%E3%80%91+%E6%A9%9F%E8%83%BD%E5%90%8D)。その際、機能提案の「背景」（機能提案の理由etc）も詳細に記入いただけますと、開発の検討がスムーズになりますので、ご協力をお願いします。

## 脆弱性を発見した場合
**Issue は作成せずに、 sofpyon at hrgrweb dot com (at と dot はそれぞれ `@` と `.` に置き換える) に連絡をお願いします。** (PortalDots 開発チームの代表メールアドレスです)

## Pull Request の作成について
- 原則として Pull Request には、対応する Issue を 1 つ以上リンクさせてください。
- 原則として、対応する Issue がない Pull Request は作成せず、まずは Issue を作成してください。
- 開発に着手する前に、開発を担当したい Issue のコメント欄にて一言お願いします。「この Issue の開発を担当したいです」など
- 既に Assignee が設定されている Issue は、他の方が開発に着手しています。Assignee が設定されていない Issue の担当をお願いします。

## Pull Request のコードレビューについて
- コードレビューは、PortalDots 開発チームの @SofPyon と @hosakou が行います。
- 以下に該当する Pull Request はレビューしません。
    - Issue がリンクされていない（PortalDots では、Issue にリンクしない Pull Request は原則として受け付けません）
    - `wip` ラベルが付与されている。
    - CircleCI で実行されるテストケースが Fail となっている。
    - [コントリビューター行動規範](https://github.com/portal-dots/PortalDots/blob/master/CODE_OF_CONDUCT.md) に反している。
- レビューにおいて `Request Changes` されると、Pull Request に `wip` ラベルが付与されます。レビュー内容に従い修正を行いましたら、 `wip` ラベルを外してください。

## 開発の流れ
1. このリポジトリを Fork した上で Clone する。
1. master ブランチから、Pull Request 提出用のブランチを作成する (命名規則 : `feature/{issue番号}_{スネークケースでの説明}` )。
    - e.g. `feature/13_update_readme`
1. 開発を行う。
    - コミットの粒度、コミットメッセージの書き方は自由です。
1. Pull Request を提出する。マージ先は master としてください。
1. PortalDots 開発チームによるレビューを受ける。
1. 問題がなければ master ブランチへマージされます。
