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
