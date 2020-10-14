-- phpMyAdmin SQL Dump
-- version 3.2.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 26, 2012 at 03:37 PM
-- Server version: 5.1.40
-- PHP Version: 5.2.12

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mycms`
--

-- --------------------------------------------------------

--
-- Table structure for table `mycms_documents`
--

CREATE TABLE IF NOT EXISTS `mycms_documents` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  `TableId` int(10) unsigned NOT NULL,
  `LeadElement` int(10) unsigned NOT NULL,
  `SortElement` int(10) unsigned NOT NULL,
  `SortDirection` tinyint(1) unsigned NOT NULL,
  `Order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=41 ;

--
-- Dumping data for table `mycms_documents`
--

INSERT INTO `mycms_documents` (`Id`, `Title`, `TableId`, `LeadElement`, `SortElement`, `SortDirection`, `Order`) VALUES
(26, 'Полезная инфа', 2, 0, 0, 0, 1),
(34, 'Справочник', 4, 0, 0, 0, 2),
(40, 'Фото и файлы', 5, 0, 0, 0, 3);

-- --------------------------------------------------------

--
-- Table structure for table `mycms_elements`
--

CREATE TABLE IF NOT EXISTS `mycms_elements` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  `DocumentId` int(10) unsigned NOT NULL,
  `FieldId` int(10) unsigned NOT NULL,
  `Type` int(3) unsigned NOT NULL,
  `Show` tinyint(1) unsigned NOT NULL,
  `Order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=20 ;

--
-- Dumping data for table `mycms_elements`
--

INSERT INTO `mycms_elements` (`Id`, `Title`, `DocumentId`, `FieldId`, `Type`, `Show`, `Order`) VALUES
(1, 'Название', 26, 1, 1, 1, 1),
(2, 'Текст', 26, 2, 2, 1, 2),
(3, 'Очередь', 26, 3, 1, 1, 3),
(9, 'Дата', 26, 17, 3, 0, 0),
(7, 'Верифицирован', 26, 18, 6, 1, 100),
(10, 'Вариант теста', 26, 19, 8, 0, 0),
(11, 'Название', 34, 15, 1, 1, 0),
(12, 'Текст', 34, 16, 2, 1, 0),
(13, 'Категория', 26, 20, 8, 0, 0),
(14, 'Цвет', 26, 21, 7, 0, 99),
(15, 'Дата', 34, 22, 3, 1, 0),
(16, 'Изображение', 40, 23, 4, 0, 2),
(17, 'Название', 40, 26, 1, 1, 1),
(18, 'Файл-отчет', 40, 25, 5, 1, 3),
(19, 'Изображение', 40, 24, 4, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `mycms_fields`
--

CREATE TABLE IF NOT EXISTS `mycms_fields` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `TableId` int(10) unsigned NOT NULL,
  `TypeId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=27 ;

--
-- Dumping data for table `mycms_fields`
--

INSERT INTO `mycms_fields` (`Id`, `Name`, `TableId`, `TypeId`) VALUES
(1, 'Title', 2, 1),
(2, 'Text', 2, 2),
(3, 'Order', 2, 3),
(17, 'Date', 2, 6),
(16, 'Text', 4, 2),
(15, 'Title', 4, 1),
(18, 'Reged', 2, 5),
(19, 'Test2Id', 2, 3),
(20, 'CategoryId', 2, 3),
(21, 'Color', 2, 1),
(22, 'Date', 4, 6),
(23, 'Image', 5, 1),
(24, 'Image100_100', 5, 1),
(25, 'File', 5, 1),
(26, 'Title', 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mycms_field_types`
--

CREATE TABLE IF NOT EXISTS `mycms_field_types` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  `Value` varchar(255) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `mycms_field_types`
--

INSERT INTO `mycms_field_types` (`Id`, `Title`, `Value`) VALUES
(1, 'Строка', 'VARCHAR( 255 )'),
(2, 'Текст', 'TEXT'),
(3, 'Целое', 'INT UNSIGNED'),
(4, 'Действительное', 'FLOAT UNSIGNED'),
(5, 'Бит', 'TINYINT( 1 ) UNSIGNED'),
(6, 'Дата', 'DATETIME');

-- --------------------------------------------------------

--
-- Table structure for table `mycms_groups`
--

CREATE TABLE IF NOT EXISTS `mycms_groups` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `mycms_groups`
--

INSERT INTO `mycms_groups` (`Id`, `Title`) VALUES
(1, 'Разработчики'),
(2, 'Администраторы');

-- --------------------------------------------------------

--
-- Table structure for table `mycms_properties`
--

CREATE TABLE IF NOT EXISTS `mycms_properties` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ElementId` int(10) unsigned NOT NULL,
  `Code` int(10) unsigned NOT NULL,
  `Value` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=45 ;

--
-- Dumping data for table `mycms_properties`
--

INSERT INTO `mycms_properties` (`Id`, `ElementId`, `Code`, `Value`) VALUES
(1, 1, 10, '150'),
(2, 2, 20, ''),
(3, 2, 21, '255'),
(4, 1, 1, '1'),
(5, 1, 2, '1'),
(6, 1, 3, ''),
(7, 1, 4, 'здесь небольшая подсказочка'),
(8, 2, 1, '1'),
(9, 2, 2, '0'),
(10, 2, 3, ''),
(11, 2, 4, ''),
(12, 2, 22, ''),
(13, 2, 23, '250'),
(14, 7, 1, '0'),
(15, 7, 2, '0'),
(16, 7, 3, '1'),
(17, 7, 4, ''),
(18, 10, 1, '0'),
(19, 10, 2, '0'),
(20, 10, 3, ''),
(21, 10, 4, ''),
(22, 10, 80, 'test2'),
(23, 10, 81, 'Title'),
(24, 10, 82, 'Id'),
(25, 10, 83, '0'),
(26, 13, 1, '0'),
(27, 13, 2, '0'),
(28, 13, 3, ''),
(29, 13, 4, ''),
(30, 13, 80, 'test'),
(31, 13, 81, 'Title'),
(32, 13, 82, 'Id'),
(33, 13, 83, '1'),
(34, 14, 1, '0'),
(35, 14, 2, '0'),
(36, 14, 3, ''),
(37, 14, 4, ''),
(38, 14, 70, 'красный; синий; пупурный; черный'),
(39, 15, 1, '0'),
(40, 15, 2, '0'),
(41, 15, 3, ''),
(42, 15, 4, ''),
(43, 15, 30, 'datetime'),
(44, 0, 3, '15');

-- --------------------------------------------------------

--
-- Table structure for table `mycms_tables`
--

CREATE TABLE IF NOT EXISTS `mycms_tables` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `mycms_tables`
--

INSERT INTO `mycms_tables` (`Id`, `Name`, `Order`) VALUES
(2, 'test', 1),
(4, 'test2', 2),
(5, 'test3', 3);

-- --------------------------------------------------------

--
-- Table structure for table `mycms_users`
--

CREATE TABLE IF NOT EXISTS `mycms_users` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Status` varchar(255) NOT NULL,
  `Login` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `GroupId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `mycms_users`
--

INSERT INTO `mycms_users` (`Id`, `Name`, `Status`, `Login`, `Password`, `GroupId`) VALUES
(1, 'Разработчик', 'Разработчик', 'roman', 'daf93dec2fed93d21a1a226fccdec724', 1);

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE IF NOT EXISTS `test` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  `Text` text NOT NULL,
  `Order` int(10) unsigned NOT NULL,
  `Date` datetime NOT NULL,
  `Reged` tinyint(1) unsigned NOT NULL,
  `Test2Id` int(10) unsigned NOT NULL,
  `CategoryId` int(10) unsigned NOT NULL,
  `Color` varchar(255) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=45 ;

--
-- Dumping data for table `test`
--

INSERT INTO `test` (`Id`, `Title`, `Text`, `Order`, `Date`, `Reged`, `Test2Id`, `CategoryId`, `Color`) VALUES
(1, 'Привет', 'Это текст записи "Привет".', 0, '0000-00-00 00:00:00', 0, 0, 0, ''),
(14, 'Привет2', 'фывфывывй', 0, '0000-00-00 00:00:00', 0, 0, 22, ''),
(3, 'Название', 'Текст', 1, '0000-00-00 00:00:00', 1, 0, 0, ''),
(20, 'Привет21', 'куку', 0, '0000-00-00 00:00:00', 1, 0, 0, ''),
(19, 'Привет11', '<p>кукува</p><p>выа</p><p>ыва</p>', 0, '0000-00-00 00:00:00', 1, 2, 0, ''),
(22, 'С вариантом теста', 'вот так', 0, '0000-00-00 00:00:00', 0, 1, 0, 'синий'),
(23, 'еще один с боксом', '<p><strong>куку</strong></p><p><strong>fgh</strong></p>', 0, '0000-00-00 00:00:00', 0, 0, 0, 'черный'),
(38, 'Тест визивига', '<p>Образец вообще как ни крути :)</p>', 0, '2003-02-20 12:00:00', 0, 0, 0, ''),
(44, 'Тест всего!', '<p>йцуцйу</p>', 0, '2012-02-23 00:00:00', 1, 1, 3, 'красный');

-- --------------------------------------------------------

--
-- Table structure for table `test2`
--

CREATE TABLE IF NOT EXISTS `test2` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  `Text` text NOT NULL,
  `Date` datetime NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=25 ;

--
-- Dumping data for table `test2`
--

INSERT INTO `test2` (`Id`, `Title`, `Text`, `Date`) VALUES
(1, 'значение справочника 1', '', '0000-00-00 00:00:00'),
(2, 'значение справочника 2', '', '0000-00-00 00:00:00'),
(3, 'значение справочника 3', '', '0000-00-00 00:00:00'),
(4, 'значение справочника 4', '', '2012-02-28 00:00:00'),
(9, 'cvfdg', '', '2000-00-00 07:00:00'),
(16, 'val4', '', '2012-02-22 15:24:00'),
(11, 'asd', '', '2000-00-00 11:43:00'),
(15, 'val1', '', '2012-02-22 15:24:00'),
(17, 'val2', '', '2012-02-22 15:24:00'),
(18, 'val3', '', '2012-02-22 15:24:00'),
(19, 'val5', '', '2012-02-22 16:36:00'),
(20, 'val6', '', '2012-02-22 16:36:00'),
(21, 'val last', '', '2012-02-22 16:37:00'),
(22, 'val7', '', '2012-02-23 02:50:00'),
(23, 'val9', '', '2012-02-23 02:50:00'),
(24, 'val8', '', '2012-02-23 02:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `test3`
--

CREATE TABLE IF NOT EXISTS `test3` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Image` varchar(255) NOT NULL,
  `Image100_100` varchar(255) NOT NULL,
  `File` varchar(255) NOT NULL,
  `Title` varchar(255) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `test3`
--

