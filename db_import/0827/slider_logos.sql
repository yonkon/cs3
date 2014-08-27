-- phpMyAdmin SQL Dump
-- version 4.2.0-rc1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Авг 28 2014 г., 00:01
-- Версия сервера: 5.5.38-0ubuntu0.14.04.1
-- Версия PHP: 5.5.9-1ubuntu4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `cart`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cscart_slider_logos`
--

CREATE TABLE IF NOT EXISTS `cscart_slider_logos` (
`slide_id` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `filename` varchar(512) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `alt` varchar(256) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `description` text,
  `filename_original` varchar(512) NOT NULL,
  `width_original` int(11) NOT NULL,
  `height_original` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cscart_slider_logos`
--
ALTER TABLE `cscart_slider_logos`
 ADD PRIMARY KEY (`slide_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cscart_slider_logos`
--
ALTER TABLE `cscart_slider_logos`
MODIFY `slide_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;