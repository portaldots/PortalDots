# <img src="https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/logo.png" alt="PortalDots" height="40" />
学園祭実行委員会と参加企画との間のコミュニケーションを支援するオープンソースウェブシステム。

<p>
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

## PortalDotsとは
PortalDots(ポータルドット)は、 **学園祭実行委員会と参加企画担当者との間のコミュニケーションを支援するウェブシステム** です。お知らせメールの一斉配信や各種申請の受付をオンラインで簡単に行うことができます。

開発は **東京理科大の学園祭実行委員経験者が主導するボランティア** の開発チームによって行っています。また、オープンソース([MIT License](https://github.com/portal-dots/PortalDots/blob/master/LICENSE))としており、無料・再配布自由としています。PortalDots は **どなたでも開発に参加いただけます** 。

## スクリーンショット
![PortalDotsスクリーンショット](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/main_screenshot.png)

### ホーム画面
![ホーム](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/screenshot_home.png)

### フォームエディター
![フォームエディター](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/screenshot_form_editor.png)

### 企画参加登録の受付画面
![企画参加登録](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/screenshot_circle_register.png)

## セットアップ方法
※もしご不明な点がありましたら、 [PortalDots 公式 LINE アカウント](https://lin.ee/aeee9s9) または **portal-dots at hrgrweb dot com** (at と dot はそれぞれ `@` と `.` に置き換える)にてサポートいたします。お気軽にご連絡ください。

1. PHP(7.3以上)、MySQL が利用できるサーバーを用意します。 [さくらのレンタルサーバー](https://www.sakura.ne.jp/) スタンダードプランなどがおすすめです。
1. [https://github.com/portal-dots/PortalDots/releases] から、最新バージョンの PortalDots をダウンロードします。 **Assets** をクリックし、 **PortalDots_v[バージョン番号].zip** というファイルをダウンロードしてください。
1. 展開したZIPファイルの中身をサーバーにアップロードします。
1. アップロードした先のページをブラウザで開きます。インストール画面が表示されるので、指示に従ってください。

## スタッフモード・管理者権限について
PortalDots には、登録ユーザーの管理や、お知らせ・配布資料ダウンロードファイルの登録を行うための「スタッフモード」があります。

また、スタッフモード内の各ページにアクセスできるユーザーを制限する「認可」機能があり、認可に関する設定を行うための「管理者権限」が存在します。管理者権限を有する「管理者ユーザー」は、PortalDots の全機能が利用できるほか、PortalDots のシステムに関する設定を変更することができます。

PortalDots 初回インストール時に作成するユーザーは管理者ユーザーとなります。

### スタッフの追加方法
スタッフを追加することができるのは、 **スタッフ・管理者ユーザー** です。

1. スタッフとしたいユーザーに、予め PortalDots にユーザー登録をしてもらいます。
1. 既にスタッフまたは管理者ユーザーとして登録しているアカウントで PortalDots にログインします。
1. サイドバー(画面左側)内の「スタッフモード」をクリックします。
1. 連絡先メールアドレス宛に届く認証コードを画面に入力します。
1. サイドバー内の「ユーザー情報管理」をクリックします。
1. スタッフとして追加したいユーザーを探し、当該ユーザーの鉛筆ボタンをクリックします。
1. 「スタッフ」を「●」に設定し、「変更の保存」をクリックします。

### 管理者ユーザーの追加方法
管理者ユーザーを追加することができるのは、 **管理者ユーザーのみ** です。

1. 管理者としたいユーザーに、予め PortalDots にユーザー登録をしてもらいます。
1. 既に管理者ユーザーとして登録しているアカウントで PortalDots にログインします。
1. サイドバー(画面左側)内の「スタッフモード」をクリックします。
1. 連絡先メールアドレス宛に届く認証コードを画面に入力します。
1. サイドバー内の「ユーザー情報管理」をクリックします。
1. スタッフとして追加したいユーザーを探し、当該ユーザーの鉛筆ボタンをクリックします。
1. 「管理者」を「●」に設定し、「変更の保存」をクリックします。

## お問い合わせ先
PortalDots 開発チーム（有志による個人開発チーム）

- [PortalDots 公式 LINE アカウント](https://lin.ee/aeee9s9)
- メール : **portal-dots at hrgrweb dot com** (at と dot はそれぞれ `@` と `.` に置き換える)
- 代表 Twitter : [@sofpyon](https://twitter.com/sofpyon)

-----

## 開発者向け情報
### PortalDots の開発に貢献(Contribution)する
PortalDots では、新規機能・既存機能改良の提案、バグの報告や Pull Request を歓迎します。

詳しくは [コントリビューションガイドライン](https://github.com/portal-dots/PortalDots/blob/master/CONTRIBUTING.md) をご覧ください。

### 開発環境セットアップ方法
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

# フロントエンド開発サーバーの起動
$ yarn watch
# Ctrl-C で停止

# 開発環境を停止する
$ yarn docker-stop
```

- `yarn docker` コマンドにより、開発環境が起動します。

### 開発環境の各種 URL
- 開発環境 : http://localhost:3000
    - 初回アクセス時、データベースエラーが表示されることがありますが、数回再読み込みすることでエラーは解消するようです。もし解消しない場合、 `yarn docker-stop` コマンドを実行してから `yarn docker` コマンドを実行し、開発環境を再起動してください。
- 開発環境から送信されるメールの確認(MailHog) : http://localhost:8025
- phpMyAdmin : http://localhost:8080
    - 開発環境の DB に作成されるデータベース名は `db_portal_dev`

-----

## ライセンス
[MIT License](https://github.com/portal-dots/PortalDots/blob/master/LICENSE)

- CodeIgniter - MIT License、一部改変
- Grocery CRUD - MIT License、一部改変
- Honoka - MIT License
