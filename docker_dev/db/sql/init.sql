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
  `role_id` int(11) NOT NULL COMMENT 'role_userテーブルのID',
  `is_authorized` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'このrole_idのroleに所属するユーザーは認可されているか',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'booths'
CREATE TABLE `booths` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `place_id` int(11) NOT NULL,
  `circle_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` int(11) unsigned NOT NULL,
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

-- Create syntax for TABLE 'circle_user'
CREATE TABLE `circle_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `circle_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_leader` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'circles'
CREATE TABLE `circles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` int(11) unsigned NOT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'documents'
CREATE TABLE `documents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` tinytext,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` int(11) unsigned NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'circleメンバーが閲覧可能なら1',
  `is_important` tinyint(1) NOT NULL DEFAULT '0',
  `schedule_id` int(11) unsigned DEFAULT NULL COMMENT 'この資料を配布したイベントのID',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'answer_details'
-- CREATE TABLE `answer_details` (
--   `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '設問回答ID',
--   `answer_id` int(11) unsigned NOT NULL COMMENT 'answers.id',
--   `question_id` int(11) unsigned NOT NULL COMMENT '設問ID',
--   `answer` longtext COMMENT '回答本文',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'answers'
-- CREATE TABLE `answers` (
--   `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '回答ID',
--   `form_id` int(11) unsigned NOT NULL COMMENT 'フォームID',
--   `created_at` datetime NOT NULL COMMENT '回答新規作成日時',
--   `updated_at` datetime NOT NULL COMMENT '回答更新日時',
--   `circle_id` int(11) unsigned NOT NULL COMMENT '回答した団体',
--   `booth_id` int(11) unsigned DEFAULT NULL COMMENT '回答したブース',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'options'
-- CREATE TABLE `options` (
--   `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '選択肢ID',
--   `question_id` int(11) unsigned NOT NULL,
--   `value` text NOT NULL,
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'questions'
-- CREATE TABLE `questions` (
--   `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '設問ID',
--   `form_id` int(11) unsigned NOT NULL COMMENT 'フォームID',
--   `name` varchar(255) NOT NULL DEFAULT '',
--   `description` text,
--   `type` varchar(255) NOT NULL DEFAULT '' COMMENT 'heading/number/text/textarea/radio/checkbox/select/upload',
--   `is_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '(heading)無視',
--   `number_min` int(11) DEFAULT NULL COMMENT '(number)範囲制限/(upload)無視/(それ以外)文字数制限',
--   `number_max` int(11) DEFAULT NULL COMMENT 'number_min と同様',
--   `allowed_types` varchar(255) DEFAULT NULL COMMENT '(uploadで使用)パイプで接続された，アップロードを許可するMIMEタイプ一覧．一般的に，ファイル拡張子はMIMEタイプとして使用できる．',
--   `max_size` int(11) unsigned DEFAULT '2048' COMMENT '(uploadで使用)キロバイトで指定．ファイル最大容量．0:制限なし',
--   `max_width` int(11) unsigned DEFAULT '0' COMMENT '(uploadで使用)最大横幅(ピクセル指定)，0:制限なし',
--   `max_height` int(11) unsigned DEFAULT '0',
--   `min_width` int(11) unsigned DEFAULT '0',
--   `min_height` int(11) unsigned DEFAULT '0',
--   `priority` int(11) unsigned NOT NULL DEFAULT '1' COMMENT 'priorityが小さい値の設問ほど上に配置される',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'forms'
-- CREATE TABLE `forms` (
--   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
--   `name` varchar(255) NOT NULL DEFAULT '',
--   `description` text,
--   `open_at` datetime NOT NULL,
--   `close_at` datetime NOT NULL,
--   `created_at` datetime NOT NULL,
--   `created_by` int(11) unsigned NOT NULL COMMENT 'フォーム作成者のユーザーID',
--   `updated_at` datetime NOT NULL,
--   `type` varchar(10) NOT NULL DEFAULT '' COMMENT 'circleかboothか',
--   `max_answers` int(11) DEFAULT NULL COMMENT '最大回答数',
--   `is_public` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '公開するか',
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'pages'
CREATE TABLE `pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` int(11) unsigned NOT NULL,
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
-- CREATE TABLE `schedules` (
--   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
--   `name` varchar(255) NOT NULL DEFAULT '',
--   `start_at` datetime NOT NULL,
--   `place` varchar(255) NOT NULL DEFAULT '',
--   `description` text,
--   `notes` text,
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'role_user'
CREATE TABLE `role_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL COMMENT 'roles.id(0:Admin)',
  `user_id` int(11) NOT NULL COMMENT 'users.id',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'roles'
CREATE TABLE `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create syntax for TABLE 'users'
-- CREATE TABLE `users` (
--   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
--   `student_id` varchar(10) DEFAULT '',
--   `name_family` varchar(255) NOT NULL DEFAULT '' COMMENT '苗字',
--   `name_family_yomi` varchar(255) NOT NULL DEFAULT '' COMMENT '苗字読み',
--   `name_given` varchar(255) NOT NULL DEFAULT '' COMMENT '名前',
--   `name_given_yomi` varchar(255) NOT NULL DEFAULT '' COMMENT '名前読み',
--   `email` varchar(255) NOT NULL DEFAULT '',
--   `password` varchar(255) NOT NULL,
--   `is_staff` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'スタッフなら 1',
--   `created_at` datetime NOT NULL,
--   `updated_at` datetime NOT NULL,
--   `reset_pass_key` text,
--   `reset_pass_key_created_at` datetime DEFAULT NULL,
--   `notes` text,
--   PRIMARY KEY (`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
