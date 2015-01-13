-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 06, 2015 at 04:20 PM
-- Server version: 5.5.37
-- PHP Version: 5.4.6-1ubuntu1.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `otp_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `call_links`
--

CREATE TABLE IF NOT EXISTS `call_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `link_timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `access_code` int(11) unsigned NOT NULL DEFAULT '0',
  `caller_name` varchar(100) DEFAULT NULL,
  `inv_special_a` varchar(100) DEFAULT NULL,
  `inv_special_b` varchar(100) DEFAULT NULL,
  `language` varchar(3) DEFAULT NULL,
  `interpreter_id` int(5) unsigned NOT NULL DEFAULT '0',
  `drop` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `callout` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `callout_number` bigint(10) unsigned NOT NULL DEFAULT '0',
  `routed_or_queued` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `answer_iid` int(4) unsigned NOT NULL DEFAULT '0',
  `answer_ext` int(4) unsigned NOT NULL DEFAULT '0',
  `submit_timestamp` datetime DEFAULT NULL,
  `inv_code` int(11) unsigned NOT NULL DEFAULT '0',
  `error` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `error_message` varchar(100) DEFAULT NULL,
  `processed` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `completed` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `ended` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Tracking Table - Collects Form Data' AUTO_INCREMENT=612707 ;

-- --------------------------------------------------------

--
-- Table structure for table `call_records`
--

CREATE TABLE IF NOT EXISTS `call_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `access_code` int(11) unsigned NOT NULL DEFAULT '0',
  `caller_name` varchar(100) NOT NULL,
  `language` varchar(3) NOT NULL,
  `interpreter_id` int(4) unsigned NOT NULL DEFAULT '0',
  `interpreter_name` varchar(100) DEFAULT NULL,
  `inv_start` datetime DEFAULT NULL,
  `inv_end` datetime DEFAULT NULL,
  `inv_special` varchar(100) DEFAULT NULL,
  `inv_detail` varchar(100) DEFAULT NULL,
  `inv_duration` int(11) unsigned NOT NULL DEFAULT '0',
  `inv_minutes` int(11) unsigned NOT NULL DEFAULT '0',
  `tot_duration` int(11) unsigned NOT NULL DEFAULT '0',
  `inv_phone` bigint(20) unsigned DEFAULT '0',
  `callout` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `callout_number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rate_code` varchar(10) DEFAULT NULL,
  `drop` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `inv_code` int(11) unsigned NOT NULL DEFAULT '0',
  `ended` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `admin` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `client_id` int(11) unsigned NOT NULL DEFAULT '0',
  `secs_on_hold` int(11) unsigned NOT NULL DEFAULT '0',
  `answer_iid` int(4) unsigned NOT NULL DEFAULT '0',
  `answer_ext` int(4) unsigned NOT NULL DEFAULT '0',
  `routed_or_queued` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `call_type` varchar(20) DEFAULT NULL,
  `error` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `error_message` text,
  `link_timestamp` datetime DEFAULT NULL,
  `start_timestamp` datetime DEFAULT NULL,
  `answer_timestamp` datetime DEFAULT NULL,
  `submit_timestamp` datetime DEFAULT NULL,
  `end_timestamp` datetime DEFAULT NULL,
  `caller_id_name` varchar(100) DEFAULT NULL,
  `caller_id_number` varchar(20) DEFAULT NULL,
  `destination_name` varchar(100) DEFAULT NULL,
  `destination_number` varchar(20) DEFAULT NULL,
  `bleg_uuid` varchar(36) DEFAULT NULL,
  `record_file_name` varchar(40) DEFAULT NULL,
  `invoiced` tinyint(4) DEFAULT '0',
  `fulfilled` tinyint(4) DEFAULT '0',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Tracking Table - Contains All Complete Calls' AUTO_INCREMENT=612704 ;

-- --------------------------------------------------------

--
-- Table structure for table `call_records_t`
--

CREATE TABLE IF NOT EXISTS `call_records_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_number` varchar(20) DEFAULT NULL,
  `access_code` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `rep_name` varchar(50) NOT NULL,
  `specialf` varchar(100) NOT NULL,
  `language` varchar(30) NOT NULL,
  `intid` int(5) NOT NULL,
  `intname` varchar(75) NOT NULL,
  `uuid` varchar(75) NOT NULL,
  `bleg_uuid` varchar(75) DEFAULT NULL,
  `orig_uuid` varchar(64) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `answer_timestamp` datetime DEFAULT NULL,
  `start_timestamp` datetime DEFAULT NULL,
  `end_timestamp` datetime DEFAULT NULL,
  `call_time` int(6) DEFAULT NULL,
  `connection_time` int(6) DEFAULT NULL,
  `duration` int(15) DEFAULT NULL,
  `billsec` int(15) DEFAULT NULL,
  `accountcode` int(15) DEFAULT NULL,
  `bbx_cdr_id` int(50) DEFAULT NULL,
  `record_file_name` varchar(75) DEFAULT NULL,
  `direction` varchar(30) DEFAULT NULL,
  `caller_id_name` varchar(75) DEFAULT NULL,
  `caller_id_number` varchar(13) DEFAULT NULL,
  `destination_name` varchar(75) DEFAULT NULL,
  `destination_number` varchar(13) DEFAULT NULL,
  `context` varchar(255) DEFAULT NULL,
  `hangup_cause` varchar(25) DEFAULT NULL,
  `read_rate` varchar(10) DEFAULT NULL,
  `read_codec` varchar(25) DEFAULT NULL,
  `write_rate` varchar(10) DEFAULT NULL,
  `write_codec` varchar(25) DEFAULT NULL,
  `drop` int(4) DEFAULT NULL,
  `callout` int(4) DEFAULT NULL,
  `admin` int(4) DEFAULT NULL,
  `co_num` varchar(15) DEFAULT '0',
  `client_name` varchar(75) DEFAULT NULL,
  `account_number` varchar(8) DEFAULT NULL,
  `invoice` varchar(5) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `rate_code` varchar(8) DEFAULT NULL,
  `r_or_i` int(3) NOT NULL,
  `connected_by` int(4) NOT NULL,
  `r_ext` int(4) NOT NULL,
  `error` tinyint(1) NOT NULL DEFAULT '0',
  `submitted` tinyint(4) NOT NULL,
  `call_type` varchar(150) DEFAULT NULL,
  `inv_detail` varchar(255) DEFAULT NULL,
  `inv_start` datetime DEFAULT NULL,
  `inv_end` datetime DEFAULT NULL,
  `inv_minutes` int(11) DEFAULT NULL,
  `inv_phone` int(12) DEFAULT NULL,
  `link_timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `job_number` (`job_number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='DEPRECATED DO NOT USE' AUTO_INCREMENT=527248 ;

-- --------------------------------------------------------

--
-- Table structure for table `client_custom`
--

CREATE TABLE IF NOT EXISTS `client_custom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL DEFAULT '0',
  `collection_id` int(10) unsigned NOT NULL DEFAULT '0',
  `show_message` varchar(50) DEFAULT NULL,
  `name_message` varchar(50) DEFAULT NULL,
  `data_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `separated` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `inv_column` text,
  `inv_report` text,
  `inv_header` text,
  `invoice_sections` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Any special requests for clients are stored here including invoice layouts and special instructions\r\ndata_type:\r\n    0: no data collected\r\n    1: text input\r\n    2: 2 text inputs\r\n    3: collection selection' AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `client_data`
--

CREATE TABLE IF NOT EXISTS `client_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access_code` int(11) NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `invoice` varchar(255) NOT NULL,
  `contract_number` varchar(255) NOT NULL,
  `client_id` int(11) NOT NULL,
  `otp_sp_in` tinyint(1) NOT NULL,
  `otp_instructions` varchar(55) DEFAULT NULL,
  `sp_type` int(11) DEFAULT NULL,
  `Invoice_Detail` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `agency` varchar(255) DEFAULT NULL,
  `division` varchar(255) DEFAULT NULL,
  `county` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) NOT NULL,
  `Div_Contact` varchar(255) DEFAULT NULL,
  `Div_Address` varchar(255) DEFAULT NULL,
  `Div_Street` varchar(255) DEFAULT NULL,
  `Div_Building` varchar(255) DEFAULT NULL,
  `Div_Suite` varchar(255) DEFAULT NULL,
  `Div_City` varchar(255) DEFAULT NULL,
  `Div_State` varchar(255) DEFAULT NULL,
  `Div_Zip` int(10) DEFAULT NULL,
  `Div_Phone` varchar(15) DEFAULT NULL,
  `Div_Fax` varchar(15) DEFAULT NULL,
  `Div_Email` varchar(255) DEFAULT NULL,
  `Bil_Contact` varchar(255) DEFAULT NULL,
  `Bil_Address` varchar(255) DEFAULT NULL,
  `Bill_To_1` varchar(255) DEFAULT NULL,
  `Bill_To_2` varchar(255) DEFAULT NULL,
  `Bill_Street` varchar(255) DEFAULT NULL,
  `Bill_Bulding_Name` varchar(255) DEFAULT NULL,
  `Bill_Suite` varchar(255) DEFAULT NULL,
  `Bill_City` varchar(255) DEFAULT NULL,
  `Bill_State` varchar(255) DEFAULT NULL,
  `Bill_Zip` int(10) DEFAULT NULL,
  `Bil_Phone` varchar(255) DEFAULT NULL,
  `Bil_Fax` varchar(255) DEFAULT NULL,
  `Bil_Email` varchar(255) DEFAULT NULL,
  `OTPLS1` decimal(19,2) NOT NULL,
  `OTPLS2` decimal(19,2) NOT NULL,
  `OTPLS3` decimal(19,2) NOT NULL,
  `OTPLS4` decimal(19,2) NOT NULL,
  `OTPLS1A` decimal(19,2) DEFAULT NULL,
  `OTPLS2A` decimal(19,2) DEFAULT NULL,
  `OTPLS3A` decimal(19,2) DEFAULT NULL,
  `OTPLS4A` decimal(19,2) DEFAULT NULL,
  `OSLS1` decimal(19,2) DEFAULT NULL,
  `OSLS2` decimal(19,2) DEFAULT NULL,
  `OSLS3` decimal(19,2) DEFAULT NULL,
  `OSLS4` decimal(19,2) DEFAULT NULL,
  `OSAHF` decimal(19,2) DEFAULT NULL,
  `OSRF` decimal(19,2) DEFAULT NULL,
  `OSRT` decimal(19,2) DEFAULT NULL,
  `OSCF` decimal(19,2) DEFAULT NULL,
  `OSCT` decimal(19,2) DEFAULT NULL,
  `Onsite_Instructions` varchar(255) DEFAULT NULL,
  `charge_callout` tinyint(1) NOT NULL,
  `rate_callout` decimal(19,2) DEFAULT NULL,
  `special_invoice` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Holds all client data' AUTO_INCREMENT=2289 ;

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE IF NOT EXISTS `collections` (
  `collection_id` varchar(50) NOT NULL DEFAULT '0',
  `key` varchar(50) NOT NULL,
  `description` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='all collections are stored here\r\nPRIMARY ids:\r\n  1: names\r\n  2: area_codes ';

-- --------------------------------------------------------

--
-- Table structure for table `interpreters`
--

CREATE TABLE IF NOT EXISTS `interpreters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iid` int(11) DEFAULT NULL,
  `btg` tinyint(4) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone_1` varchar(14) DEFAULT NULL,
  `phone_2` varchar(14) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `lid` int(11) DEFAULT NULL,
  `language_code` varchar(255) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL,
  `on_call` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1054 ;

-- --------------------------------------------------------

--
-- Table structure for table `interpreter_archive`
--

CREATE TABLE IF NOT EXISTS `interpreter_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interpreter_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `phone_1` bigint(20) unsigned NOT NULL,
  `language` varchar(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2830 ;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `language` varchar(255) NOT NULL,
  `language_code` varchar(255) NOT NULL,
  `language_set` varchar(255) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=217 ;

-- --------------------------------------------------------

--
-- Table structure for table `names`
--

CREATE TABLE IF NOT EXISTS `names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5165 ;

-- --------------------------------------------------------

--
-- Table structure for table `special_dropdown`
--

CREATE TABLE IF NOT EXISTS `special_dropdown` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `counties` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=116 ;

-- --------------------------------------------------------

--
-- Table structure for table `status_change`
--

CREATE TABLE IF NOT EXISTS `status_change` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intid` int(11) NOT NULL,
  `time_of_change` datetime NOT NULL,
  `changed_to` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=905 ;

-- --------------------------------------------------------

--
-- Table structure for table `timeclock`
--

CREATE TABLE IF NOT EXISTS `timeclock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intid` int(5) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status_type` int(3) NOT NULL,
  `status_name` varchar(30) NOT NULL,
  `time_punch` tinyint(4) NOT NULL,
  `this_punch` datetime NOT NULL,
  `next_punch` datetime DEFAULT NULL,
  `edit_punch` datetime DEFAULT NULL,
  `next_time_punch` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intid` int(4) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `username` varchar(75) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(75) NOT NULL,
  `level` int(2) NOT NULL,
  `ext` int(4) NOT NULL,
  `pin` int(4) NOT NULL,
  `langs` int(2) NOT NULL,
  `lang` varchar(50) NOT NULL,
  `class` int(3) NOT NULL,
  `last_punch_id` int(11) DEFAULT NULL,
  `DR_REP` tinyint(4) NOT NULL,
  `auth_token` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intid` (`intid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=201 ;

-- --------------------------------------------------------

--
-- Table structure for table `us_area_codes`
--

CREATE TABLE IF NOT EXISTS `us_area_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(10) unsigned NOT NULL DEFAULT '0',
  `state` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Listing of area codes and the state they belong to' AUTO_INCREMENT=605 ;

-- --------------------------------------------------------

--
-- Table structure for table `verified_call_archive`
--

CREATE TABLE IF NOT EXISTS `verified_call_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `link_timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `access_code` int(11) unsigned NOT NULL DEFAULT '0',
  `caller_name` varchar(100) DEFAULT NULL,
  `inv_special_a` varchar(100) DEFAULT NULL,
  `inv_special_b` varchar(100) DEFAULT NULL,
  `language` varchar(3) DEFAULT NULL,
  `interpreter_id` int(5) unsigned NOT NULL DEFAULT '0',
  `drop` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `callout` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `callout_number` bigint(10) unsigned NOT NULL DEFAULT '0',
  `routed_or_queued` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `answer_iid` int(4) unsigned NOT NULL DEFAULT '0',
  `answer_ext` int(4) unsigned NOT NULL DEFAULT '0',
  `submit_timestamp` datetime DEFAULT NULL,
  `processed` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `completed` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `inv_code` int(11) unsigned NOT NULL DEFAULT '0',
  `ended` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Tracking Table - Archives of Collected Data After Completion' AUTO_INCREMENT=612704 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
