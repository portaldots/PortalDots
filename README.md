<h1 align="center">PortalDots</h1>
<p align="center">イベント参加サークルへのお知らせ配信、ウェブフォームによる申請受付ができるオープンソースウェブシステム。</p>

<p align="center">
    <a href="https://circleci.com/gh/portal-dots/PortalDots">
        <img src="https://circleci.com/gh/portal-dots/PortalDots.svg?style=svg" alt="CircleCI" />
    </a>
    <a href="https://codecov.io/gh/portal-dots/PortalDots">
        <img src="https://codecov.io/gh/portal-dots/PortalDots/branch/master/graph/badge.svg" />
    </a>
    <a href="https://opensource.org/licenses/MIT">
        <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License: MIT" />
    </a>
</p>

![PortalDots](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/portal-dots-eyecatch.png)

![スクリーンショット](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/screenshots-home-v2.png)

## これは何？
- [野田地区理大祭](https://nodaridaisai.com) という大学祭の実行委員会の業務を効率化するためのウェブシステム。
    - 団体向け : 団体向け会議の次回予告、各種配布資料ダウンロード、お知らせの閲覧、各種申請の提出、お問い合わせなどを行うことができる。
    - 実行委員向け : 登録されているユーザー情報の閲覧、参加団体・企画情報の管理など。また、お問い合わせを LINE Notify で受け取る機能もある。
- 「平成30年度野田地区新入生歓迎ガイダンス」などで実運用されていた。

## 開発環境セットアップ方法
Git、PHP(7.3以上)、Composer、Node.js、Yarn、Docker がセットアップ済みである必要があります。

```bash
$ git clone git@github.com:portal-dots/PortalDots.git
$ cd PortalDots/

# 設定ファイルを作成
$ cp .env.example .env
$ php artisan key:generate

# 必要パッケージをインストール
$ composer install
$ yarn install

# 開発環境を起動する
$ yarn docker

# マイグレーション
#
# ⚠️ α版注）データベース構造に関して、現在破壊的な変更を継続して行なっています。
# 　もしデータベースエラーが発生する場合、開発環境を再ビルドしてから
# 　代わりに `yarn migrate:refresh` コマンドを実行してください。
$ yarn migrate

# フロントエンド用アセットのコンパイル
$ yarn watch
# Ctrl-C で停止

# 開発環境を停止する
$ yarn docker-stop
```

- `yarn docker` コマンドにより、開発環境が起動します。

## 開発環境の各種 URL
- 開発環境 : http://localhost:3000
    - 初回アクセス時、データベースエラーが表示されることがありますが、数回再読み込みすることでエラーは解消するようです。もし解消しない場合、 `yarn docker-stop` コマンドを実行してから `yarn docker` コマンドを実行し、開発環境を再起動してください。
- 開発環境から送信されるメールの確認(MailHog) : http://localhost:8025
- phpMyAdmin : http://localhost:8080
    - 開発環境の DB に作成されるデータベース名は `db_portal_dev`

## スタッフモード・管理者権限について
PortalDots には、登録ユーザーの管理や、お知らせ・会議での配布資料ダウンロードファイルの登録を行うための「スタッフモード」があります。

また、スタッフモード内の各ページにアクセスできるユーザーを制限する「認可」機能があり、認可に関する設定を行うための「管理者権限」が存在します。

現状、PortalDots の GUI では、一番最初のスタッフや管理者を登録できませんので、以下の方法でスタッフや管理者を設定してください。

1. PortalDots 上で、通常通りユーザー登録を行う
1. `users` テーブル上の `is_staff` 列を `1` にする
    - → そのユーザーは「スタッフ」になり、サイドバーにスタッフモードへアクセスするためのボタンが出現する
    - phpMyAdmin などで操作してください
1. `roles` テーブルに、 `id` が `0` のレコードを作成する (`name` は `管理者` など、お好きなものを設定してください)
1. `role_user` テーブルに、 `user_id` を管理者にしたいユーザーの ID、 `role_id` を `0` にしたレコードを作成する
    - → そのユーザーは「管理者」になります

## PortalDots の開発に貢献する
PortalDots では、Issue や Pull Request を歓迎します。

今のところ、Issue 提出や Pull Request 提出に関して、特段のルールは設けておりません（今後設ける可能性はある）ので、何かございましたらお気軽にお願いします！

## 既知の問題
- ~~メール文面等に「新歓実行委員会」などという単語がハードコーディングされている箇所がある可能性があります~~ **(おそらく解消済)**
- ~~スタッフモードのフォーム作成ページから申請フォームを作成できない~~ **(フォームエディターで申請フォームを作成できます)**
    - ~~申請フォームを作成するには、スタッフモードの [申請] ページでフォームを新規作成した上で、データベース上の `questions` にデータを手動で登録する必要があります。~~
- 「ブース単位」で受け付ける申請フォームが作成できない
- 申請フォームの回答数制限が機能していない
    - 回答数制限は「1」を設定してください
- ~~一度、スタッフモードにアクセスするための 2 段階認証をクリアすると、ログアウト後も、 2 段階認証がクリアされた状態が継続してしまう~~ **(解消済)**
    - ~~この問題は、現在実運用されている [新歓ウェブポータル](https://swp.x0.com) には存在しません~~
- 管理者権限を持った状態でスタッフモードにアクセスすると、サイドバーに「ESS」という謎の項目が出現し、それをクリックすると謎のウェブページが表示される
    - ESS は、おそらく "Execute SQL Statements" の略称として付けられたと見られており、某サークルとは一切関係ありません。
- PHPDoc の内容と実際のシグネチャが一致していないものがある
- 中途半端に phpcs によるコード整形を行なったため、一部のコードが崩れている
- LINE Notify への通知機能で、`try-catch` による適切な例外処理が行われていない

その他の問題は、開発者の GitHub プライベートリポジトリの issue で管理されています。今後、このリポジトリに issue を移行するかもしれません。

## 将来的に実装したい機能
- 場所登録機能
    - 上記と同様です

## 雑多なメモ書き
- ~~平文のパスワードを格納する時の変数名は `$plain_password` とする~~
    - Laravel のコードでは `$password` が標準っぽい？
- (CodeIgniter) `$this->db` のメソッドはモデルで実行、 `$this->input` はコントローラでのみ使う
- (CodeIgniter) `_validate_*` 系メソッド(独自バリデータ)は必ず `public` にすること。 `private` や `protected` ではエラーになる
- 「イベント」という用語と「スケジュール」という用語が混同していますが，ほぼ同じ意味(だと思います)
- 変数名は snake_case にする

## その他
このプロジェクトは、まだプログラミングスキルが浅かった時に開発されたものを、最近になって多くのリファクタリングを施したものです。今でもあまり良いコードとは言えないかもしれませんが、これでもだいぶマシになったほうです(パスワードがハードコードされていたりした)。

## SQL
PortalDots を動作させるために必要な DB テーブルは、`composer migrate` コマンドによって作成されます。

# ライセンス

(このセクションは書きかけです)

MIT License

```
MIT License

Copyright (c) 2019-2020 Soji Takahashi (SofPyon), hosakou and contributors

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

- CodeIgniter - MIT License、一部改変
- Grocery CRUD - MIT License、一部改変
- Honoka - MIT License
