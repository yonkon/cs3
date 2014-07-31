-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июл 31 2014 г., 20:07
-- Версия сервера: 5.1.68-cll-lve
-- Версия PHP: 5.5.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `ctotop39_agent3`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cscart_support_tickets`
--

CREATE TABLE IF NOT EXISTS `cscart_support_tickets` (
  `user_id` int(11) NOT NULL,
  `question_type` varchar(1) NOT NULL DEFAULT 'q' COMMENT 'q - question, t - technical question, p - payment',
  `theme` varchar(128) NOT NULL,
  `message` text NOT NULL,
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `file_path` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
