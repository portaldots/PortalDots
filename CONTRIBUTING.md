# コントリビューションガイドライン (不具合報告や改善要望などについて)
Issue や Pull Request の作成は大歓迎です！

## 不具合報告・改善要望を送りたい！ (Issue について)
- 不具合、要望などがあれば Issue を作成してください。文章のフォーマットは自由ですが、提案の背景や内容を具体的に記入しただけますと対応がスムーズになります。Issue の作成には GitHub アカウントが必要です。
    - [GitHub アカウントの作成はこちら](https://github.com/signup)
    - [Issue の作成はこちら](https://github.com/portal-dots/PortalDots/issues/new)
- Issue を作成せずに Pull Request を作成していただいても構いません
    - ただし、内容によっては Pull Request をマージせず Close させていただく場合があります。誤字脱字や明らかな不具合の修正ではない場合、機能修正の提案という形で Issue を作成していただけると、Pull Request がマージされないという事態を防げるかと思います。

## PortalDots のコードを直接修正したい！ (Pull Request について)
- Pull Request のベース（マージ先）ブランチは、基本的には `3.x` としてください。
- 下記の場合、ベースブランチは `4.x` としてください。
    - マイグレーションファイルを新規作成した場合
    - UI上のデザインや大幅な文言修正を行なった場合（誤字脱字の修正・軽微な修正以外）

### 開発の流れ (一例)
1. このリポジトリを Fork した上で Clone する。
1. ベースブランチから、Pull Request 提出用のブランチを作成する (命名規則 : `feature/{issue番号}_{スネークケースでの説明}` )。
    - e.g. `feature/13_update_readme`
1. 開発を行う。
    - コミットの粒度、コミットメッセージの書き方は自由です。
1. Pull Request を提出する。

## コントリビューター行動規範
PortalDots では「コントリビューター行動規範」を定めています。Issue や Pull Request の作成前に一読していただけますと幸いです。

- [コントリビューター行動規範](https://github.com/portal-dots/PortalDots/blob/3.x/CODE_OF_CONDUCT.md)
