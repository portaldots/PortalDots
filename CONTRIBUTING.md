# コントリビューションガイドライン
PortalDots は、Issue や Pull Request の作成大歓迎です！

## 不具合を発見した場合
[こちらから Issue を作成してください](https://github.com/portal-dots/PortalDots/issues/new?assignees=&labels=bug&template=bug.md&title=%E3%80%90%E7%94%BB%E9%9D%A2%E5%90%8D%E3%80%91+%E4%B8%8D%E5%85%B7%E5%90%88%E3%81%AE%E5%86%85%E5%AE%B9)

## 新機能の提案をしたい場合
[こちらから Issue を作成してください](https://github.com/portal-dots/PortalDots/issues/new?assignees=&labels=enhancement&template=enhancement.md&title=%E3%80%90%E7%94%BB%E9%9D%A2%E5%90%8D%E3%80%91+%E6%A9%9F%E8%83%BD%E5%90%8D)。その際、機能提案の「背景」（機能提案の理由etc）も詳細に記入いただけますと、開発の検討がスムーズになりますので、ご協力をお願いします。

## 脆弱性を発見した場合
**Issue は作成せずに、 sofpyon at hrgrweb dot com (at と dot はそれぞれ `@` と `.` に置き換える) に連絡をお願いします。** (PortalDots 開発チームの代表メールアドレスです)

## Pull Request の作成について
- 明らかに PortalDots のバグであり、かつ修正がご自身で可能な場合、 Issue を作成せず、 Pull Request を作成してください。
- 新機能提案・比較的大規模なコード修正の提案の場合、 Pull Request を作成する前に Issue を作成してください。 実装を行うかどうかについての検討を Issue 上で行います。
- 対応する Issue がある Pull Request には、本文中に `close #issue番号` という文言を入れてください。
- 他のコントリビューターとのバッティングを防ぐために、開発に着手する際は、開発を担当したい Issue のコメント欄にて一言お願いします。「この Issue の開発を担当したいです」など
- 既に Assignee が設定されている Issue は、他の方が開発に着手しています。Assignee が設定されていない Issue の担当をお願いします。

## Pull Request のコードレビューについて
- コードレビューは、PortalDots 開発チームの @SofPyon と @hosakou が行います。
- 以下に該当する Pull Request はレビューしません。
    - Draft 状態の Pull Request
    - CircleCI でテストが実行中、またはテストケースが Fail となっている Pull Request
    - [コントリビューター行動規範](https://github.com/portal-dots/PortalDots/blob/master/CODE_OF_CONDUCT.md) に反している Pull Request
- レビュワーがレビューにおいてコードの修正をお願いすることがあります。レビュー内容に従い修正を行いましたら、コメントにてレビュワーに一言お願いします。 GitHub の `Request a Review` でも構いません。

## 開発の流れ
1. このリポジトリを Fork した上で Clone する。
1. master ブランチから、Pull Request 提出用のブランチを作成する (命名規則 : `feature/{issue番号}_{スネークケースでの説明}` )。
    - e.g. `feature/13_update_readme`
1. 開発を行う。
    - コミットの粒度、コミットメッセージの書き方は自由です。
1. Pull Request を提出する。マージ先は 3.x としてください。
1. PortalDots 開発チームによるレビューを受ける。
1. 問題がなければ 3.x ブランチへマージされます。
