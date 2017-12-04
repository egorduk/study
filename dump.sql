-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               5.6.29-log - MySQL Community Server (GPL)
-- ОС Сервера:                   Win32
-- HeidiSQL Версия:              9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры базы данных study
CREATE DATABASE IF NOT EXISTS `study` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `study`;


-- Дамп структуры для таблица study.auction_bid
CREATE TABLE IF NOT EXISTS `auction_bid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `date_auction` datetime NOT NULL,
  `user_order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=1024;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.author_file
CREATE TABLE IF NOT EXISTS `author_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `date_upload` datetime NOT NULL,
  `size` varchar(10) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_author_file_user_id` (`user_id`),
  CONSTRAINT `FK_author_file_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=8192;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.cancel_request
CREATE TABLE IF NOT EXISTS `cancel_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_create` datetime NOT NULL,
  `comment` varchar(255) NOT NULL,
  `is_together_apply` bit(1) NOT NULL,
  `percent` int(11) NOT NULL,
  `verdict` varchar(255) NOT NULL,
  `creator` int(11) NOT NULL,
  `is_show` bit(1) NOT NULL,
  `user_order_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=138;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.country
CREATE TABLE IF NOT EXISTS `country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `mobile_code` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=3276;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.favorite_order
CREATE TABLE IF NOT EXISTS `favorite_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_favorite` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_order_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=3276;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.help_data
CREATE TABLE IF NOT EXISTS `help_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `days_verdict` int(11) NOT NULL,
  `days_guarantee` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.mail_option
CREATE TABLE IF NOT EXISTS `mail_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_orders` bit(1) NOT NULL,
  `chat_response` bit(1) NOT NULL,
  `date_edit` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_mail_option_user_id` (`user_id`),
  CONSTRAINT `FK_mail_option_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=16384;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.openid
CREATE TABLE IF NOT EXISTS `openid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `profile_url` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `identity` varchar(255) NOT NULL,
  `photo_big` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=3276;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.order_file
CREATE TABLE IF NOT EXISTS `order_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `date_upload` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `size` varchar(10) NOT NULL,
  `is_delete` bit(1) NOT NULL DEFAULT b'0',
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_order_file_user_order` (`order_id`),
  KEY `FK_order_file_user` (`user_id`),
  CONSTRAINT `FK_order_file_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_order_file_user_order` FOREIGN KEY (`order_id`) REFERENCES `user_order` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=129;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.provider
CREATE TABLE IF NOT EXISTS `provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=2340;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.select_bid
CREATE TABLE IF NOT EXISTS `select_bid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_select` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_bid_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.status_order
CREATE TABLE IF NOT EXISTS `status_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `code` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=2048;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.subject_order
CREATE TABLE IF NOT EXISTS `subject_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_name` varchar(50) NOT NULL,
  `parent_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `child_name` (`child_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=2730;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.type_order
CREATE TABLE IF NOT EXISTS `type_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` varchar(8) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=2730;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.type_ps
CREATE TABLE IF NOT EXISTS `type_ps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` varchar(5) NOT NULL,
  `info` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=4096;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(12) NOT NULL,
  `password` char(88) NOT NULL,
  `email` varchar(80) NOT NULL,
  `date_reg` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_confirm_recovery` datetime NOT NULL,
  `date_confirm_reg` datetime NOT NULL,
  `date_upload_avatar` datetime NOT NULL,
  `ip_reg` int(11) unsigned NOT NULL,
  `salt` varchar(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `is_confirm` tinyint(1) NOT NULL DEFAULT '0',
  `hash_code` varchar(60) NOT NULL DEFAULT '',
  `token` varchar(30) NOT NULL,
  `recovery_password` char(100) NOT NULL DEFAULT '',
  `account` int(11) NOT NULL,
  `is_ban` tinyint(1) NOT NULL DEFAULT '0',
  `is_access_order` tinyint(1) NOT NULL DEFAULT '0',
  `avatar` varchar(25) NOT NULL DEFAULT 'default.png',
  `rating_point` int(11) NOT NULL,
  `user_rating_id` int(11) NOT NULL,
  `user_info_id` int(11) NOT NULL,
  `role` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `email` (`email`),
  KEY `FK_user_user_info` (`user_info_id`),
  CONSTRAINT `FK_user_user_info` FOREIGN KEY (`user_info_id`) REFERENCES `user_info` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=8192;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.user_bid
CREATE TABLE IF NOT EXISTS `user_bid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sum` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `comment` varchar(150) NOT NULL,
  `date_bid` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_client_date` bit(1) NOT NULL DEFAULT b'0',
  `is_show_author` bit(1) NOT NULL DEFAULT b'1',
  `is_show_client` bit(1) NOT NULL DEFAULT b'1',
  `is_select_client` bit(1) NOT NULL DEFAULT b'0',
  `is_confirm_author` bit(1) NOT NULL DEFAULT b'0',
  `is_confirm_fail` bit(1) NOT NULL DEFAULT b'0',
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_bid_user` (`user_id`),
  KEY `FK_user_bid_user_order` (`order_id`),
  CONSTRAINT `FK_user_bid_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_user_bid_user_order` FOREIGN KEY (`order_id`) REFERENCES `user_order` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=528;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.user_info
CREATE TABLE IF NOT EXISTS `user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `skype` varchar(20) NOT NULL,
  `mobile_phone` varchar(17) NOT NULL,
  `static_phone` varchar(17) NOT NULL,
  `username` varchar(20) NOT NULL,
  `surname` varchar(20) NOT NULL,
  `lastname` varchar(20) NOT NULL,
  `country_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_info_country` (`country_id`),
  CONSTRAINT `FK_user_info_country` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=4096;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.user_order
CREATE TABLE IF NOT EXISTS `user_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` int(11) unsigned NOT NULL,
  `theme` varchar(255) NOT NULL,
  `task` text NOT NULL,
  `originality` int(11) unsigned NOT NULL,
  `count_sheet` int(11) unsigned NOT NULL,
  `date_create` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_expire` datetime NOT NULL,
  `date_edit` datetime NOT NULL,
  `date_complete` datetime NOT NULL,
  `date_guarantee` datetime NOT NULL,
  `date_cancel` datetime NOT NULL,
  `date_confirm` datetime NOT NULL,
  `is_show_author` bit(1) NOT NULL DEFAULT b'1',
  `is_show_client` bit(1) NOT NULL DEFAULT b'1',
  `is_delay` bit(1) NOT NULL DEFAULT b'0',
  `is_hide` bit(1) NOT NULL DEFAULT b'0',
  `files_folder` varchar(20) NOT NULL,
  `additional_info` varchar(255) NOT NULL,
  `client_comment` varchar(100) NOT NULL,
  `client_degree` int(11) NOT NULL,
  `subject_order_id` int(11) NOT NULL,
  `type_order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_order_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num` (`num`),
  KEY `FK_user_order_status_order` (`status_order_id`),
  KEY `FK_user_order_user` (`user_id`),
  KEY `FK_user_order_type_order` (`type_order_id`),
  KEY `FK_user_order_subject_order` (`subject_order_id`),
  CONSTRAINT `FK_user_order_status_order` FOREIGN KEY (`status_order_id`) REFERENCES `status_order` (`id`),
  CONSTRAINT `FK_user_order_subject_order` FOREIGN KEY (`subject_order_id`) REFERENCES `subject_order` (`id`),
  CONSTRAINT `FK_user_order_type_order` FOREIGN KEY (`type_order_id`) REFERENCES `type_order` (`id`),
  CONSTRAINT `FK_user_order_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=16384;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.user_ps
CREATE TABLE IF NOT EXISTS `user_ps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `num` varchar(20) NOT NULL,
  `date_create` datetime NOT NULL,
  `date_edit` datetime NOT NULL,
  `type_ps_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=2048;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.user_raiting
CREATE TABLE IF NOT EXISTS `user_raiting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=16384;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.user_role
CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(15) NOT NULL,
  `code` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=8192;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица study.webchat_message
CREATE TABLE IF NOT EXISTS `webchat_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(255) NOT NULL,
  `date_write` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_read` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `writer_id` int(11) NOT NULL,
  `response_id` int(11) NOT NULL,
  `user_order_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=54;

-- Экспортируемые данные не выделены.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
