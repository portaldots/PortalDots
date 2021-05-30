# <img src="https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/logo.png" alt="PortalDots" height="40" />
学園祭実行委員会と参加企画との間のコミュニケーションを支援するオープンソースウェブシステム。

<p>
    <a href="https://circleci.com/gh/portal-dots/PortalDots">
        <img src="https://circleci.com/gh/portal-dots/PortalDots.svg?style=svg" alt="CircleCI" />
    </a>
    <a href="https://codecov.io/gh/portal-dots/PortalDots">
        <img src="https://codecov.io/gh/portal-dots/PortalDots/branch/3.x/graph/badge.svg" />
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
- [PortalDots 公式ウェブサイト](https://www.portaldots.com)

## スクリーンショット
![PortalDotsスクリーンショット](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/main_screenshot.png)

### ホーム画面
![ホーム](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/screenshot_home.png)

### フォームエディター
![フォームエディター](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/screenshot_form_editor.png)

### 企画参加登録の受付画面
![企画参加登録](https://raw.githubusercontent.com/portal-dots/PortalDots/master/docs/readme/screenshot_circle_register.png)

## セットアップ方法
※ 「ロリポップ！レンタルサーバー」や「コアサーバー」で PortalDots を利用する方法は [PortalDots マニュアル](https://www.portaldots.com/docs/setup/) に掲載しています。

※ もしご不明な点がありましたら、 [PortalDots 公式 LINE アカウント](https://lin.ee/aeee9s9) または **portal-dots at hrgrweb dot com** (at と dot はそれぞれ `@` と `.` に置き換える)にてサポートいたします。お気軽にご連絡ください。

1. PHP(7.3以上)、MySQL(5.7以上) が利用できるサーバーを用意します。 [ロリポップ！レンタルサーバー](https://lolipop.jp/) ライトプラン、[コアサーバー](https://www.coreserver.jp/) CORE-X、[さくらのレンタルサーバ](https://www.sakura.ne.jp/) スタンダードプランでの動作を確認しています。
1. PortalDots をダウンロードします。 **[ここをクリックすると最新バージョンをダウンロードできます](https://github.com/portal-dots/PortalDots/releases/latest/download/PortalDots.zip)**
1. 展開したZIPファイルの中身をサーバーにアップロードします。
    - **注意 (macOSを利用の場合)** — ZIPファイルを展開した後、Finder上でキーボードの<kbd>command</kbd> + <kbd>shift</kbd> + <kbd>.(ピリオド)</kbd>キーを押すと、不可視ファイル(半透明のファイル)が表示されます。サーバー上には、 **この不可視ファイルも含めてアップロードしてください** 。
1. アップロードした先のページをブラウザで開きます。インストール画面が表示されるので、指示に従ってください。

## スタッフモード・管理者権限について
PortalDots には、登録ユーザーの管理や、お知らせ・配布資料ダウンロードファイルの登録を行うための「スタッフモード」があります。

また、スタッフモード内の各機能を利用できるユーザーを制限する「スタッフの権限設定」機能があります。管理者権限を有する「管理者ユーザー」は、PortalDots の全機能が利用できるほか、PortalDots のシステムに関する設定を変更することができます。

PortalDots 初回インストール時に作成するユーザーは管理者ユーザーとなります。

### スタッフの追加方法
スタッフを追加することができるのは、 **スタッフ・管理者ユーザー** です。

1. スタッフとしたいユーザーに、予め PortalDots にユーザー登録をしてもらいます。
1. 既にスタッフまたは管理者ユーザーとして登録しているアカウントで PortalDots にログインします。
1. サイドバー(画面左側)内の「スタッフモード」をクリックします。
1. 連絡先メールアドレス宛に届く認証コードを画面に入力します。
1. サイドバー内の「ユーザー情報管理」をクリックします。
1. スタッフとして追加したいユーザーを探し、当該ユーザーの鉛筆ボタンをクリックします。
1. 「ユーザー種別」を「スタッフ」に変更し、「保存」をクリックします。

### 管理者ユーザーの追加方法
管理者ユーザーを追加することができるのは、 **管理者ユーザーのみ** です。

1. 管理者としたいユーザーに、予め PortalDots にユーザー登録をしてもらいます。
1. 既に管理者ユーザーとして登録しているアカウントで PortalDots にログインします。
1. サイドバー(画面左側)内の「スタッフモード」をクリックします。
1. 連絡先メールアドレス宛に届く認証コードを画面に入力します。
1. サイドバー内の「ユーザー情報管理」をクリックします。
1. スタッフとして追加したいユーザーを探し、当該ユーザーの鉛筆ボタンをクリックします。
1. 「ユーザー種別」を「管理者」に変更し、「保存」をクリックします。

※ セキュリティのため、管理者権限を割り当てるユーザーの人数は最小限にしてください。

### 「スタッフの権限設定」について
「ユーザー種別」が「スタッフ」のユーザーは、「管理者」ユーザーに「スタッフの権限設定」を行ってもらうまで、スタッフモードの各種機能は利用できません。管理者ユーザーは、以下に記載の方法で、スタッフユーザーが各種機能を利用できるように設定してください。

1. 既に管理者ユーザーとして登録しているアカウントで PortalDots にログインします。
1. サイドバー(画面左側)内の「スタッフモード」をクリックします。
1. 連絡先メールアドレス宛に届く認証コードを画面に入力します。
1. サイドバー内の「スタッフの権限設定」をクリックします。
1. スタッフモードの機能を利用できるように設定したいユーザーを探し、当該ユーザーの鉛筆ボタンをクリックします。
1. そのユーザーが利用できるようにする機能にチェックを入れ、「保存」をクリックします。

※ セキュリティのため、各ユーザーが利用できる機能は最小限にしてください。例えば、企画情報の確認ができれば十分なユーザーには、企画の編集ができない「スタッフモード › 企画情報管理 › 閲覧」という権限を付与してください。

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
Git、PHP(7.4以上)、Node.js、Yarn、Docker がセットアップ済みである必要があります。

セットアップが完了した後、以下のコマンドを順番に実行してください。

```bash
$ git clone git@github.com:portal-dots/PortalDots.git
$ cd PortalDots/

# 必要な Node.js パッケージをインストール
# ※ エラーが表示される場合は、後述の *1 を参照してください
$ yarn install

# 設定ファイルを作成
$ cp .env.example .env
$ php artisan key:generate

# 開発環境を起動する
$ yarn docker

# マイグレーション(データベースのセットアップ)
$ yarn migrate

# Docker コンテナ内で必要な PHP パッケージをインストール
$ yarn docker-bash
$ composer install
$ exit

# フロントエンド開発サーバーの起動
$ yarn hot
# → ブラウザで http://localhost にアクセスすると、PortalDots の開発環境が起動する
# → フロントエンド開発サーバーを終了するには Ctrl + C を押す

# 開発環境を停止する
$ yarn docker-stop
```

#### *1 : `yarn install` 実行時にエラーが表示される場合
インストールされている Node.js のバージョンが古い場合、エラーが表示されることがあります。

Node.js を最新バージョンにアップグレードした上で、再度 `yarn install` を実行してください。

### 開発環境の各種 URL
- 開発環境 : http://localhost
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
