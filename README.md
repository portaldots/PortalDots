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

![PortalDots](https://user-images.githubusercontent.com/6687653/83792485-142e1d00-a6d6-11ea-94a6-7fb3f712252a.png)

## PortalDotsとは
PortalDots(ポータルドット)は、 **学園祭実行委員会と参加企画担当者との間のコミュニケーションを支援するウェブシステム** です。お知らせメールの一斉送信や各種申請の受付をオンラインで簡単に行うことができます。

開発は **東京理科大の学園祭実行委員経験者が主導するボランティア** の開発チームによって行っています。また、オープンソース([MIT License](https://github.com/portal-dots/PortalDots/blob/master/LICENSE))としており、無料・再配布自由としています。PortalDots は **どなたでも開発に参加いただけます** 。

- **[最新バージョンをダウンロード](https://github.com/portal-dots/PortalDots/releases/latest/download/PortalDots.zip)**
- [PortalDots 公式ウェブサイト](https://dots.soji.dev)

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

1. PHP(7.3以上)、MySQL(5.7以上) が利用できるサーバーを用意します。 [さくらのレンタルサーバ](https://www.sakura.ne.jp/) スタンダードプランでの動作を確認しています。
1. PortalDots をダウンロードします。 **[ここをクリックすると最新バージョンをダウンロードできます](https://github.com/portal-dots/PortalDots/releases/latest/download/PortalDots.zip)**
1. 展開したZIPファイルの中身をサーバーにアップロードします。
    - **注意 (macOSを利用の場合)** — ZIPファイルを展開した後、Finder上でキーボードの<kbd>command</kbd> + <kbd>shift</kbd> + <kbd>.(ピリオド)</kbd>キーを押すと、不可視ファイル(半透明のファイル)が表示されます。サーバー上には、 **この不可視ファイルも含めてアップロードしてください** 。
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

セットアップが完了した後、以下のコマンドを順番に実行してください。

```bash
$ git clone git@github.com:portal-dots/PortalDots.git
$ cd PortalDots/

# 必要な PHP パッケージをインストール
# ※ エラーが表示される場合は、後述の *1 を参照してください
$ composer install

# 必要な Node.js パッケージをインストール
# ※ エラーが表示される場合は、後述の *2 を参照してください
$ yarn install

# 設定ファイルを作成
$ cp .env.example .env
$ php artisan key:generate

# 開発環境を起動する
$ yarn docker

# マイグレーション(データベースのセットアップ)
$ yarn migrate

# フロントエンド開発サーバーの起動
$ yarn watch
# Ctrl-C で停止

# 開発環境を停止する
$ yarn docker-stop
```

#### *1 : `composer install` 実行時にエラーが表示される場合
以下の PHP 拡張モジュールがインストールされていない場合、 `composer install` の実行に失敗することがあります。

(追加で拡張モジュールのインストールが必要な場合、当該モジュールの名称が `composer install` コマンド実行時に表示されることがあります)

- BCMath PHP拡張
- Ctype PHP拡張
- Fileinfo PHP拡張
- JSON PHP拡張
- Mbstring PHP拡張
- OpenSSL PHP拡張
- PDO PHP拡張
- Tokenizer PHP拡張
- XML PHP拡張
- GD PHP拡張
- ZIP PHP拡張

#### *2 : `yarn install` 実行時にエラーが表示される場合
インストールされている Node.js のバージョンが古い場合、エラーが表示されることがあります。

Node.js を最新バージョンにアップグレードした上で、再度 `yarn install` を実行してください。

### 開発環境の各種 URL
- 開発環境 : http://localhost:3000
    - 初回アクセス時、データベースエラーが表示されることがありますが、数回再読み込みすることでエラーは解消するようです。もし解消しない場合、 `yarn docker-stop` コマンドを実行してから `yarn docker` コマンドを実行し、開発環境を再起動してください。
- 開発環境から送信されるメールの確認(MailHog) : http://localhost:8025
- phpMyAdmin : http://localhost:8080
    - 開発環境の DB に作成されるデータベース名は `db_portal_dev`

### スタッフ・管理者ユーザーの作成方法
※ スタッフ・管理者についての詳細は、前述の「スタッフモード・管理者権限について」を参照してください。

1. 開発環境の PortalDots の画面上で、通常通りユーザー登録を行います。
1. http://localhost:8080 にアクセスし、phpMyAdmin を開きます。
1. 画面左のサイドバーから `db_portal_dev` をクリックします。
1. 続けて表示されるテーブル一覧から `users` をクリックします。
1. スタッフ・管理者にしたいユーザーを探し、以下の操作を行います。
    - **通常のスタッフユーザーにする:** `is_staff` 列の `0` をダブルクリックし、 `1` に書き換えて Enter キーを押します。
    - **管理者ユーザーにする:** `is_staff` 列と `is_admin` 列の両方について `0` をダブルクリックし、 `1` に書き換えて Enter キーを押します。

-----

## ライセンス
[MIT License](https://github.com/portal-dots/PortalDots/blob/master/LICENSE)

- CodeIgniter - MIT License、一部改変
- Grocery CRUD - MIT License、一部改変
- Honoka - MIT License
