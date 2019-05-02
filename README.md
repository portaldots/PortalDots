<h1 align="center">inaka-portal | ridaisai_portal</h1>
<p align="center">オープンソース版 ridaisai_portal</p>

![inaka-portal](docs/inaka-portal-eyecatch.png)

## これは何？
- [野田地区理大祭](https://nodaridaisai.com) という大学祭の実行委員会の業務を効率化するためのウェブシステム。
    - 団体向け : 団体向け会議の次回予告、各種配布資料ダウンロード、お知らせの閲覧、各種申請の提出、お問い合わせなどを行うことができる。
    - 実行委員向け : 登録されているユーザー情報の閲覧、参加団体・企画情報の管理など。また、お問い合わせを LINE Notify で受け取る機能もある。
- 「平成30年度野田地区新入生歓迎ガイダンス」などで実運用されていた。
- inaka-portal として公開するにあたり、ある程度のリファクタリングなどを施した。が、まだ途中。
- 2019 年 5 月 1 日現在公開されている [理大祭ウェブポータル](https://portal.nodaridaisai.com/login) とは別システム。
    - こちらの「理大祭ウェブポータル」は、このプロジェクトの第 2 世代にあたるが、あまり機能を優れていない。
    - 余談だが、 [理大祭ウェブポータル](https://portal.nodaridaisai.com/login) に掲載されている「配布資料」を読むと、この `inaka-portal` をより一層お楽しみいただける(?)

## 開発環境セットアップ方法
Git、Composer、Docker がセットアップ済みである必要があります。

```bash
# git clone ...
$ cd inaka-portal/

# 設定ファイルを作成
$ cp application/.env.development application/.env
# LINE Notify 通知を利用したい場合、LINE Notify 管理画面でトークンを発行し、
# 発行されたトークンを .env の RP_LINE_NOTIFY_TOKEN で設定してください。

# 必要パッケージをインストール
$ composer install

# 開発環境を起動する
$ composer docker

# 開発環境を停止する
$ composer docker-stop
```

- `composer docker` コマンドにより、開発環境が起動します。データベース上には、必要なテーブルが自動で作成されます。

## 開発環境の各種 URL
- 開発環境 : http://localhost
    - 初回アクセス時、データベースエラーが表示されることがありますが、数回再読み込みすることでエラーは解消するようです。もし解消しない場合、 `composer docker-stop` コマンドを実行してから `composer docker` コマンドを実行し、開発環境を再起動してください。
- 開発環境から送信されるメールの確認(MailHog) : http://localhost:8025
- phpMyAdmin : http://localhost:8080
    - 開発環境の DB に作成されるデータベース名は `db_portal_dev`

## スタッフモード・管理者権限について
inaka-portal には、登録ユーザーの管理や、お知らせ・会議での配布資料ダウンロードファイルの登録を行うための「スタッフモード」があります。

また、スタッフモード内の各ページにアクセスできるユーザーを制限する「認可」機能があり、認可に関する設定を行うための「管理者権限」が存在します。

現状、inaka-portal の GUI 上で、一番最初のスタッフや管理者を登録する手段はありませんので、以下の方法でスタッフや管理者を設定してください。

1. inaka-portal 上で、通常通りユーザー登録を行う
1. `users` テーブル上の `is_staff` 列を `1` にする → そのユーザーは「スタッフ」になり、サイドバーにスタッフモードへアクセスするためのボタンが出現する
    - phpMyAdmin などで操作してください
1. `user_roles_list` テーブルに、 `id` が `0` のレコードを作成する (`name` は `管理者` など、お好きなものを設定してください)
1. `user_roles` テーブルに、 `user_id` を管理者にしたいユーザーの ID、 `role_id` を `0` にしたレコードを作成する → そのユーザーは「管理者」になります

## 既知の問題
- メール文面等に「新歓実行委員会」などという単語がハードコーディングされている箇所がある可能性があります
- スタッフモードのフォーム作成ページから申請フォームを作成できない
    - 申請フォームを作成するには、スタッフモードの [申請] ページでフォームを新規作成した上で、データベース上の `form_sections` や `form_questions` にデータを手動で登録する必要があります。
- 「ブース単位」で受け付ける申請フォームが作成できない
- 申請フォームの回答数制限が機能していない
- 一度、スタッフモードにアクセスするための 2 段階認証をクリアすると、ログアウト後も、 2 段階認証がクリアされた状態が継続してしまう
    - この問題は、現在実運用されている [新歓ウェブポータル](https://swp.x0.com) には存在しません
- 管理者権限を持った状態でスタッフモードにアクセスすると、サイドバーに「ESS」という謎の項目が出現し、それをクリックすると謎のウェブページが表示される
    - ESS は、おそらく "Execute SQL Statements" の略称として付けられたと見られており、某サークルとは一切関係ありません。
- PHPDoc の内容と実際のシグネチャが一致していないものがある
- 中途半端に phpcs によるコード整形を行なったため、一部のコードが崩れている
- LINE Notify への通知機能で、`try-catch` による適切な例外処理が行われていない

その他の問題は、開発者の GitHub プライベートリポジトリの issue で管理されています。今後、このリポジトリに issue を移行するかもしれません。

## 将来的に実装したい機能
- 申請フォーム作成 GUI
- 団体登録機能
- 場所登録機能

## 雑多なメモ書き
- 平文のパスワードを格納する時の変数名は `$plain_password` とする
- `$this->db` のメソッドはモデルで実行、 `$this->input` はコントローラでのみ使う
- `_validate_*` 系メソッド(独自バリデータ)は必ず `public` にすること。 `private` や `protected` ではエラーになる
- 「イベント」という用語と「スケジュール」という用語が混同していますが，ほぼ同じ意味(だと思います)

## その他
このプロジェクトは、まだプログラミングスキルが浅かった時に開発されたものを、最近になって多くのリファクタリングを施したものです。今でもあまり良いコードとは言えないかもしれませんが、これでもだいぶマシになったほうです(パスワードがハードコードされていたりした)。

## SQL
データベースにテーブルを作成するためのSQLは以下のとおりです．

同様の内容は `docker_dev/db/sql/init.sql` に格納されています。

```sql
-- Create syntax for TABLE 'auth_staff_page'
CREATE TABLE `auth_staff_page` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `main_page_type` varchar(255) NOT NULL DEFAULT '' COMMENT '認可設定をする対象のmain_page_type',
  `is_authorized` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:原則不認可(ホワイトリスト使用)、1:原則認可(ブラックリスト使用)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'auth_staff_role'
CREATE TABLE `auth_staff_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `auth_staff_page_id` int(11) NOT NULL COMMENT 'auth_staff_page の ID',
  `role_id` int(11) NOT NULL COMMENT 'user_rolesテーブルのID',
  `is_authorized` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'このrole_idのroleに所属するユーザーは認可されているか',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'booths'
CREATE TABLE `booths` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `place_id` int(11) NOT NULL,
  `circle_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `modified_at` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'ci_sessions'
CREATE TABLE `ci_sessions` (
  `id` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `data` blob NOT NULL,
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'circle_members'
CREATE TABLE `circle_members` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `circle_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'circle_members_roles'
CREATE TABLE `circle_members_roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `circle_member_id` int(11) NOT NULL COMMENT 'circle_members.id',
  `role_id` int(11) DEFAULT NULL COMMENT 'circle_members_roles_list.id',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'circle_members_roles_list'
CREATE TABLE `circle_members_roles_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'circles'
CREATE TABLE `circles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `modified_at` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'documents'
CREATE TABLE `documents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` tinytext,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `created_by` int(11) NOT NULL COMMENT 'user_id',
  `created_at` datetime NOT NULL,
  `modified_at` datetime NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'circleメンバーが閲覧可能なら1',
  `is_important` tinyint(1) NOT NULL DEFAULT '0',
  `schedule_id` int(11) unsigned DEFAULT NULL COMMENT 'この資料を配布したイベントのID',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'form_answer_details'
CREATE TABLE `form_answer_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '設問回答ID',
  `answer_id` int(11) unsigned NOT NULL COMMENT 'form_answers.id',
  `question_id` int(11) unsigned NOT NULL COMMENT '設問ID',
  `answer` longtext COMMENT '回答本文',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'form_answers'
CREATE TABLE `form_answers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '回答ID',
  `form_id` int(11) unsigned NOT NULL COMMENT 'フォームID',
  `created_at` datetime NOT NULL COMMENT '回答新規作成日時',
  `modified_at` datetime NOT NULL COMMENT '回答更新日時',
  `circle_id` int(11) unsigned NOT NULL COMMENT '回答した団体',
  `booth_id` int(11) unsigned DEFAULT NULL COMMENT '回答したブース',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'form_question_options'
CREATE TABLE `form_question_options` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '選択肢ID',
  `question_id` int(11) unsigned NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'form_questions'
CREATE TABLE `form_questions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '設問ID',
  `section_id` int(11) unsigned NOT NULL COMMENT 'セクションID',
  `name` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT 'number/text/textarea/radio/checkbox/select/upload',
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `number_min` int(11) DEFAULT NULL COMMENT '(number)範囲制限/(upload)無視/(それ以外)文字数制限',
  `number_max` int(11) DEFAULT NULL COMMENT 'number_min と同様',
  `allowed_types` varchar(255) DEFAULT NULL COMMENT '(uploadで使用)パイプで接続された，アップロードを許可するMIMEタイプ一覧．一般的に，ファイル拡張子はMIMEタイプとして使用できる．',
  `max_size` int(11) unsigned DEFAULT '2048' COMMENT '(uploadで使用)キロバイトで指定．ファイル最大容量．0:制限なし',
  `max_width` int(11) unsigned DEFAULT '0' COMMENT '(uploadで使用)最大横幅(ピクセル指定)，0:制限なし',
  `max_height` int(11) unsigned DEFAULT '0',
  `min_width` int(11) unsigned DEFAULT '0',
  `min_height` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'form_sections'
CREATE TABLE `form_sections` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'セクションID',
  `form_id` int(11) unsigned NOT NULL COMMENT 'フォームID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT 'セクションタイトル',
  `description` text COMMENT '説明文',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'forms'
CREATE TABLE `forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `open_at` datetime NOT NULL,
  `close_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `modified_at` datetime NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT '' COMMENT 'circleかboothか',
  `max_answers` int(11) DEFAULT NULL COMMENT '最大回答数',
  `is_public` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '公開するか',
  `created_by` int(11) unsigned NOT NULL COMMENT 'フォーム作成者のユーザーID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'pages'
CREATE TABLE `pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `modified_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL COMMENT 'user_id',
  `modified_by` int(11) NOT NULL COMMENT 'user_id',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'places'
CREATE TABLE `places` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `type` int(2) NOT NULL DEFAULT '0' COMMENT '1:屋内、2:屋外、3:特殊場所',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'schedules'
CREATE TABLE `schedules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `start_at` datetime NOT NULL,
  `place` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'user_roles'
CREATE TABLE `user_roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'users.id',
  `role_id` int(11) NOT NULL COMMENT 'user_roles_list.id(0:Admin)',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'user_roles_list'
CREATE TABLE `user_roles_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'users'
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` varchar(10) DEFAULT '',
  `name_family` varchar(255) NOT NULL DEFAULT '' COMMENT '苗字',
  `name_family_yomi` varchar(255) NOT NULL DEFAULT '' COMMENT '苗字読み',
  `name_given` varchar(255) NOT NULL DEFAULT '' COMMENT '名前',
  `name_given_yomi` varchar(255) NOT NULL DEFAULT '' COMMENT '名前読み',
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  `is_staff` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'スタッフなら 1',
  `created_at` datetime NOT NULL,
  `modified_at` datetime NOT NULL,
  `reset_pass_key` text,
  `reset_pass_key_created_at` datetime DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'users_pre'
CREATE TABLE `users_pre` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` varchar(10) DEFAULT '',
  `name_family` varchar(255) NOT NULL DEFAULT '' COMMENT '苗字',
  `name_family_yomi` varchar(255) NOT NULL DEFAULT '' COMMENT '苗字読み',
  `name_given` varchar(255) NOT NULL DEFAULT '' COMMENT '名前',
  `name_given_yomi` varchar(255) NOT NULL DEFAULT '' COMMENT '名前読み',
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  `verifycode_univemail` text COMMENT '認証コード',
  `verifycode_email` text COMMENT '認証コード',
  `created_at` datetime NOT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

# ライセンス

(このセクションは書きかけです)

MIT License

```
Copyright 2019 Soji Takahashi (SofPyon)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```

- CodeIgniter - MIT License
- Grocery CRUD - MIT License、一部改変
