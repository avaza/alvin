--
-- Database: `alvin`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth_email` varchar(75) NOT NULL,
  `auth_passw` varchar(40) NOT NULL,
  `auth_creds` varchar(255) DEFAULT NULL,
  `auth_level` int(1) unsigned NOT NULL DEFAULT '1',
  `auth_atmpt` int(1) unsigned NOT NULL DEFAULT '0',
  `auth_block` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_email` (`auth_email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='System Authentication Table' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NULL DEFAULT NULL,
  `code` varchar(20) NULL DEFAULT NULL,
  `type` varchar(20) NULL DEFAULT NULL,
  `fname` varchar(100) NULL DEFAULT NULL,
  `lname` varchar(100) NULL DEFAULT NULL,
  `email` varchar(100) NULL DEFAULT NULL,
  `phone` varchar(10) NULL DEFAULT NULL,
  `phext` varchar(10) NULL DEFAULT NULL,
  `phon2` varchar(10) NULL DEFAULT NULL,
  `phex2` varchar(10) NULL DEFAULT NULL,
  `ph2fx` tinyint(4) NOT NULL DEFAULT '0',
  `street1` varchar(100) NULL DEFAULT NULL,
  `street2` varchar(100) NULL DEFAULT NULL,
  `state` varchar(2) NULL DEFAULT NULL,
  `city` varchar(100) NULL DEFAULT NULL,
  `zip` varchar(5) NULL DEFAULT NULL,
  `status` varchar(20) NULL DEFAULT NULL,
  `invite` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Profile and contact details for Employees, Contractors, and Customers' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE IF NOT EXISTS `divisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access_code` int(11) NOT NULL,
  `name` varchar(100) NULL DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `client_id` int(11) NOT NULL,
  `contact_id` int(11) NULL DEFAULT NULL,
  `collect_req` varchar(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `access_code` (`access_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Holds all Agency-Divisions data' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `agencies`
--

CREATE TABLE IF NOT EXISTS `agencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_code` int(11) NOT NULL,
  `name` varchar(100) NULL DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `account_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `collect_req` varchar(255) NULL DEFAULT NULL,
  `invoice_req` varchar(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_code` (`client_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Holds all Account-Agencies data' AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_code` int(11) NOT NULL,
  `name` varchar(100) NULL DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `contact_id` int(11) NOT NULL,
  `collect_req` varchar(255) NULL DEFAULT NULL,
  `invoice_req` varchar(255) NULL DEFAULT NULL,
  `contract` varchar(255) NULL DEFAULT NULL,
  `other_fees` varchar(255) NULL DEFAULT NULL,
  `after_hours` varchar(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_code` (`account_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Holds all Client-Accounts data' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NULL DEFAULT NULL,
  `code` varchar(3) NULL DEFAULT NULL,
  `info` text NULL,  -- JSON additional language information (Later)
  `phonetic` varchar(50) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `language_code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Relational - Contacts and Languages (Interpreter Rates)' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `language_rates`
--

CREATE TABLE IF NOT EXISTS `languages_rates` (
  `account_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `subject_type` varchar(10) NULL DEFAULT NULL,
  `otp_minute` decimal(2,2) NULL,
  `vri_minute` decimal(2,2) NULL,
  `ons_hour` decimal(3,2) NULL,
  `tsl_word` decimal(2,2) NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Relational - Language Rates for Clients (accounts) and Interpreters (contacts)' AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `calls`
--

CREATE TABLE IF NOT EXISTS `calls` (

-- Original 'calls' record items (Never change)

  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `name` varchar(100) NULL DEFAULT NULL,
  `req1` varchar(100) NULL DEFAULT NULL,
  `req2` varchar(100) NULL DEFAULT NULL,
  `lang` varchar(100) NULL DEFAULT NULL,
  `numb` varchar(100) NULL DEFAULT NULL,
  `type` varchar(100) NULL DEFAULT NULL,
  `fail` varchar(100) NULL DEFAULT NULL,
  `byid` int(11) unsigned NOT NULL DEFAULT '0',
  `toid` int(11) unsigned NOT NULL DEFAULT '0',
  `code` int(11) unsigned NOT NULL DEFAULT '0',
  `invoice` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NULL ON UPDATE CURRENT_TIMESTAMP,
  `dropped` datetime NULL DEFAULT NULL,
  `cancelled` datetime NULL DEFAULT NULL,
  `submitted` datetime NULL DEFAULT NULL,
  `connected` datetime NULL DEFAULT NULL,
  `completed` datetime NULL DEFAULT NULL,

-- Cudatel Static Record Details

  `cuda_caller` varchar(100) NULL DEFAULT NULL, -- JSON "caller id" names
  `cuda_number` varchar(20) NULL DEFAULT NULL,  -- JSON "caller id" numbers
  `cuda_stamps` varchar(255) NULL DEFAULT NULL, -- JSON timestamps for call
  `cuda_vitals` varchar(255) NULL DEFAULT NULL, -- JSON delimited call data
  `cuda_uuid_a` varchar(255) NULL DEFAULT NULL, -- JSON A leg uuids for call
  `cuda_uuid_b` varchar(255) NULL DEFAULT NULL, -- JSON B leg uuids for call

-- Calculated Time Values

  `connect_seconds` int(11) unsigned NOT NULL DEFAULT '0',
  `session_seconds` int(11) unsigned NOT NULL DEFAULT '0',
  `invoice_seconds` int(11) unsigned NOT NULL DEFAULT '0',
  `payment_seconds` int(11) unsigned NOT NULL DEFAULT '0',
  `invoice_begin` datetime NULL DEFAULT NULL,
  `invoice_final` datetime NULL DEFAULT NULL,
  `payment_begin` datetime NULL DEFAULT NULL,
  `payment_final` datetime NULL DEFAULT NULL,

  -- Current record Status/error information

  `invoice_code` int(11) unsigned NOT NULL DEFAULT '0',
  `fail_message` varchar(255) NULL DEFAULT NULL, -- JSON Call calculation error/flag information
  `invoice_complete` tinyint(4) NOT NULL DEFAULT '0',
  `payment_complete` tinyint(4) NOT NULL DEFAULT '0',
  `deleted` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='Contains Data for all OTP Sessions' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `subject_id` varchar(25) NOT NULL,
  `subject_type` varchar(25) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Notes - For ALL Tables & ALL Records' AUTO_INCREMENT=1 ;