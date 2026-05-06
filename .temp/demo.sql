-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Mar 15, 2026 at 12:43 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `piprapay`
--

-- --------------------------------------------------------

--
-- Table structure for table `pp_addon`
--

CREATE TABLE `pp_addon` (
  `id` int(11) NOT NULL,
  `addon_id` varchar(15) NOT NULL,
  `slug` varchar(40) NOT NULL DEFAULT '--',
  `name` text,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_addon_parameter`
--

CREATE TABLE `pp_addon_parameter` (
  `id` int(11) NOT NULL,
  `addon_id` varchar(15) NOT NULL,
  `option_name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_admin`
--

CREATE TABLE `pp_admin` (
  `id` int(11) NOT NULL,
  `a_id` varchar(15) NOT NULL,
  `full_name` text NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` text NOT NULL,
  `temp_password` text,
  `reset_limit` varchar(10) NOT NULL DEFAULT '3',
  `status` enum('active','suspend') NOT NULL DEFAULT 'active',
  `role` enum('admin','staff') NOT NULL DEFAULT 'admin',
  `2fa_status` enum('enable','disable') NOT NULL DEFAULT 'disable',
  `2fa_secret` varchar(20) NOT NULL DEFAULT '--',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pp_admin`
--

INSERT INTO `pp_admin` (`id`, `a_id`, `full_name`, `username`, `email`, `password`, `temp_password`, `reset_limit`, `status`, `role`, `2fa_status`, `2fa_secret`, `created_date`, `updated_date`) VALUES
(1, '0784264068', 'PipraPay', 'admin', 'admin@demo.com', '$2y$10$PPore3UbDRKDRq7CYC4DjujMSHTQUPsNPNDzaj5iYLDMG0df/JncG', '$2y$10$dsplQ4xS1BC8T7d1tkB61.RT5ORqWKGJ1GyNCBXjV3e6JQ6FiXxS.', '3', 'active', 'admin', 'disable', '72WYO3RE7ZRVSPDW', '2026-03-15 00:41:10', '2026-03-15 00:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `pp_api`
--

CREATE TABLE `pp_api` (
  `id` int(11) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `name` text NOT NULL,
  `api_key` varchar(60) NOT NULL,
  `expired_date` text,
  `api_scopes` text NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_balance_verification`
--

CREATE TABLE `pp_balance_verification` (
  `id` int(11) NOT NULL,
  `device_id` varchar(15) NOT NULL,
  `sender_key` varchar(15) NOT NULL,
  `type` enum('Personal','Agent','Merchant') NOT NULL DEFAULT 'Personal',
  `current_balance` decimal(20,8) NOT NULL,
  `simslot` varchar(6) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_brands`
--

CREATE TABLE `pp_brands` (
  `id` int(11) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `favicon` text,
  `logo` text,
  `identify_name` varchar(50) NOT NULL DEFAULT 'Default',
  `name` text,
  `support_email_address` text,
  `support_phone_number` text,
  `support_website` text,
  `whatsapp_number` text,
  `telegram` text,
  `facebook_messenger` text,
  `facebook_page` text,
  `theme` varchar(120) NOT NULL DEFAULT 'twenty-six',
  `street_address` text,
  `city_town` text,
  `postal_code` text,
  `country` text,
  `timezone` varchar(150) NOT NULL DEFAULT 'Asia/Dhaka',
  `language` varchar(150) NOT NULL DEFAULT 'en',
  `currency_code` varchar(150) NOT NULL DEFAULT 'BDT',
  `autoExchange` enum('disabled','enabled') NOT NULL DEFAULT 'disabled',
  `payment_tolerance` varchar(150) NOT NULL DEFAULT '0',
  `created_date` varchar(20) NOT NULL DEFAULT '--',
  `updated_date` varchar(20) NOT NULL DEFAULT '--'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pp_brands`
--

INSERT INTO `pp_brands` (`id`, `brand_id`, `favicon`, `logo`, `identify_name`, `name`, `support_email_address`, `support_phone_number`, `support_website`, `whatsapp_number`, `telegram`, `facebook_messenger`, `facebook_page`, `theme`, `street_address`, `city_town`, `postal_code`, `country`, `timezone`, `language`, `currency_code`, `autoExchange`, `payment_tolerance`, `created_date`, `updated_date`) VALUES
(1, '6657227357', '--', '--', 'Default', '--', '--', '--', '--', '--', '--', '--', '--', 'twenty-six', '--', '--', '--', '--', 'Asia/Dhaka', 'en', 'BDT', 'disabled', '0', '2026-03-15 00:41:10', '2026-03-15 00:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `pp_browser_log`
--

CREATE TABLE `pp_browser_log` (
  `id` int(11) NOT NULL,
  `a_id` varchar(15) NOT NULL,
  `cookie` varchar(40) NOT NULL,
  `browser` varchar(10) NOT NULL,
  `device` varchar(10) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `status` enum('active','expired') NOT NULL DEFAULT 'active',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pp_browser_log`
--

INSERT INTO `pp_browser_log` (`id`, `a_id`, `cookie`, `browser`, `device`, `ip`, `status`, `created_date`, `updated_date`) VALUES
(1, '0784264068', '32cd62142d5faa6442cf1187666b4605', 'Chrome', 'Desktop', '::1', 'active', '2026-03-15 00:41:32', '2026-03-15 00:41:32');

-- --------------------------------------------------------

--
-- Table structure for table `pp_currency`
--

CREATE TABLE `pp_currency` (
  `id` int(11) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `code` varchar(6) NOT NULL,
  `symbol` varchar(5) NOT NULL,
  `rate` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pp_currency`
--

INSERT INTO `pp_currency` (`id`, `brand_id`, `code`, `symbol`, `rate`, `created_date`, `updated_date`) VALUES
(1, '6657227357', 'BDT', '৳', '0.00000000', '2026-03-15 00:41:10', '2026-03-15 00:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `pp_customer`
--

CREATE TABLE `pp_customer` (
  `id` int(11) NOT NULL,
  `ref` varchar(15) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `status` enum('active','suspend') NOT NULL DEFAULT 'active',
  `suspend_reason` text,
  `inserted_via` enum('manual','checkout') NOT NULL DEFAULT 'manual',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_device`
--

CREATE TABLE `pp_device` (
  `id` int(11) NOT NULL,
  `d_id` varchar(40) NOT NULL,
  `device_id` varchar(15) NOT NULL,
  `otp` varchar(15) NOT NULL,
  `name` text,
  `model` text,
  `android_level` text,
  `app_version` text,
  `status` enum('processing','used') NOT NULL DEFAULT 'processing',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL,
  `last_sync` varchar(20) NOT NULL DEFAULT '--'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_domain`
--

CREATE TABLE `pp_domain` (
  `id` int(11) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_env`
--

CREATE TABLE `pp_env` (
  `id` int(11) NOT NULL,
  `brand_id` varchar(15) NOT NULL DEFAULT 'both',
  `option_name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pp_env`
--

INSERT INTO `pp_env` (`id`, `brand_id`, `option_name`, `value`, `created_date`, `updated_date`) VALUES
(1, 'both', 'geneal-application-settings-paymentPath', '--', '2026-03-15 00:41:21', '2026-03-15 00:41:21'),
(2, 'both', 'geneal-application-settings-invoicePath', '--', '2026-03-15 00:41:21', '2026-03-15 00:41:21'),
(3, 'both', 'geneal-application-settings-paymentLinkPath', '--', '2026-03-15 00:41:21', '2026-03-15 00:41:21'),
(4, 'both', 'geneal-application-settings-adminPath', '--', '2026-03-15 00:41:21', '2026-03-15 00:41:21'),
(5, 'both', 'geneal-application-settings-cronPath', '--', '2026-03-15 00:41:21', '2026-03-15 00:41:21'),
(6, 'both', 'geneal-application-settings-homepageRedirect', '--', '2026-03-15 00:41:21', '2026-03-15 00:41:21'),
(7, 'both', 'last-cron-invocation', '--', '2026-03-15 00:41:33', '2026-03-15 00:41:33');

-- --------------------------------------------------------

--
-- Table structure for table `pp_faq`
--

CREATE TABLE `pp_faq` (
  `id` int(11) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_gateways`
--

CREATE TABLE `pp_gateways` (
  `id` int(11) NOT NULL,
  `gateway_id` varchar(15) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `slug` varchar(40) NOT NULL DEFAULT '--',
  `name` text,
  `display` text,
  `logo` text,
  `currency` varchar(6) NOT NULL,
  `min_allow` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `max_allow` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `fixed_discount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `percentage_discount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `fixed_charge` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `percentage_charge` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `primary_color` text,
  `text_color` text,
  `btn_color` text,
  `btn_text_color` text,
  `tab` enum('mfs','bank','global') NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pp_gateways`
--

INSERT INTO `pp_gateways` (`id`, `gateway_id`, `brand_id`, `slug`, `name`, `display`, `logo`, `currency`, `min_allow`, `max_allow`, `fixed_discount`, `percentage_discount`, `fixed_charge`, `percentage_charge`, `primary_color`, `text_color`, `btn_color`, `btn_text_color`, `tab`, `status`, `created_date`, `updated_date`) VALUES
(1, '4148359435', '6657227357', 'bkash-api-tokenized', 'Bkash Api (Tokenized)', 'Bkash Api (Tokenized)', 'https://piprapay.local/pp-content/pp-modules/pp-gateways/bkash-api-tokenized/assets/logo.jpg', 'BDT', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '#D12053', '#FFFFFF', '#D12053', '#FFFFFF', 'mfs', 'active', '2026-03-15 00:42:00', '2026-03-15 00:42:00'),
(2, '0176054863', '6657227357', 'bkash-personal', 'Bkash Personal', 'Bkash Personal', 'https://piprapay.local/pp-content/pp-modules/pp-gateways/bkash-personal/assets/logo.jpg', 'BDT', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '#D12053', '#FFFFFF', '#D12053', '#FFFFFF', 'mfs', 'active', '2026-03-15 00:42:31', '2026-03-15 00:42:31');

-- --------------------------------------------------------

--
-- Table structure for table `pp_gateways_parameter`
--

CREATE TABLE `pp_gateways_parameter` (
  `id` int(11) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `gateway_id` varchar(15) NOT NULL,
  `option_name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_invoice`
--

CREATE TABLE `pp_invoice` (
  `id` int(11) NOT NULL,
  `ref` varchar(30) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `customer_info` text,
  `gateway_id` varchar(15) NOT NULL DEFAULT '--',
  `currency` text NOT NULL,
  `due_date` text,
  `shipping` varchar(250) NOT NULL DEFAULT '0',
  `status` enum('paid','unpaid','refunded','canceled') NOT NULL,
  `note` text,
  `private_note` text,
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_invoice_items`
--

CREATE TABLE `pp_invoice_items` (
  `id` int(11) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `invoice_id` varchar(30) NOT NULL,
  `description` text,
  `amount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `discount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `vat` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_payment_link`
--

CREATE TABLE `pp_payment_link` (
  `id` int(11) NOT NULL,
  `ref` varchar(30) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `product_info` text NOT NULL,
  `amount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `currency` text NOT NULL,
  `expired_date` text NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_payment_link_field`
--

CREATE TABLE `pp_payment_link_field` (
  `id` int(11) NOT NULL,
  `paymentLinkID` varchar(30) NOT NULL,
  `formType` text NOT NULL,
  `fieldName` text NOT NULL,
  `value` text NOT NULL,
  `required` enum('true','false') NOT NULL DEFAULT 'true',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_permission`
--

CREATE TABLE `pp_permission` (
  `id` int(11) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `a_id` varchar(15) NOT NULL,
  `permission` text NOT NULL,
  `status` enum('active','suspend') NOT NULL DEFAULT 'active',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pp_permission`
--

INSERT INTO `pp_permission` (`id`, `brand_id`, `a_id`, `permission`, `status`, `created_date`, `updated_date`) VALUES
(1, '6657227357', '0784264068', '{\"resources\":{\"customers\":{\"create\":true,\"edit\":true,\"delete\":true},\"transaction\":{\"edit\":true,\"delete\":true,\"approve\":true,\"cancel\":true,\"refund\":true,\"send_ipn\":true},\"invoice\":{\"create\":true,\"edit\":true,\"delete\":true},\"payment_link\":{\"create\":true,\"edit\":true,\"delete\":true},\"gateways\":{\"create\":true,\"edit\":true,\"delete\":true},\"addons\":{\"create\":true,\"edit\":true,\"delete\":true},\"brand_settings\":{\"view\":true,\"edit\":true},\"api_settings\":{\"view\":true,\"create\":true,\"edit\":true,\"delete\":true},\"theme_settings\":{\"view\":true,\"edit\":true},\"faq_settings\":{\"view\":true,\"create\":true,\"edit\":true,\"delete\":true},\"currency_settings\":{\"view\":true,\"sync_rate\":true,\"import\":true,\"edit\":true},\"sms_data\":{\"create\":true,\"edit\":true,\"delete\":true},\"device\":{\"connect\":true,\"delete\":true,\"balance_verification_for\":true},\"brands\":{\"create\":true,\"edit\":true,\"delete\":true},\"staff\":{\"create\":true,\"edit\":true,\"delete\":true,\"assign_brand_to\":true,\"edit_permission\":true,\"view_permission_list\":true,\"delete_permission_of\":true},\"domains\":{\"whitelist\":true,\"edit\":true,\"delete\":true},\"system_settings\":{\"manage_general\":true,\"manage_cron\":true,\"manage_update\":true,\"manage_import\":true}},\"pages\":{\"dashboard\":true,\"reports\":true,\"customers\":true,\"transaction\":true,\"invoice\":true,\"payment_link\":true,\"gateways\":true,\"addons\":true,\"brand_settings\":true,\"sms_data\":true,\"device\":true,\"brands\":true,\"staff_management\":true,\"domains\":true,\"system_settings\":true}}', 'active', '2026-03-15 00:41:10', '2026-03-15 00:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `pp_sms_data`
--

CREATE TABLE `pp_sms_data` (
  `id` int(11) NOT NULL,
  `source` enum('app','web') NOT NULL DEFAULT 'web',
  `device_id` varchar(15) NOT NULL,
  `sender` varchar(15) NOT NULL DEFAULT '--',
  `sender_key` varchar(15) NOT NULL,
  `simslot` text,
  `number` varchar(20) NOT NULL DEFAULT '--',
  `amount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `currency` varchar(10) NOT NULL DEFAULT '--',
  `trx_id` varchar(100) NOT NULL DEFAULT '--',
  `balance` varchar(70) NOT NULL DEFAULT '--',
  `message` text,
  `reason` text,
  `type` enum('Personal','Agent','Merchant') NOT NULL DEFAULT 'Personal',
  `entry_type` enum('manual','automatic') NOT NULL DEFAULT 'automatic',
  `edit_status` enum('done','pending') NOT NULL DEFAULT 'pending',
  `status` enum('approved','awaiting-review','used','error') NOT NULL DEFAULT 'approved',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_transaction`
--

CREATE TABLE `pp_transaction` (
  `id` int(11) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `source` enum('invoice','payment-link','payment-link-default','api') NOT NULL DEFAULT 'api',
  `ref` varchar(30) NOT NULL,
  `customer_info` text NOT NULL,
  `amount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `processing_fee` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `discount_amount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `local_net_amount` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `currency` text,
  `local_currency` text,
  `sender` varchar(50) NOT NULL DEFAULT '--',
  `trx_id` varchar(70) NOT NULL DEFAULT '--',
  `trx_slip` text,
  `gateway_id` varchar(50) NOT NULL DEFAULT '--',
  `sender_key` varchar(50) NOT NULL DEFAULT '--',
  `sender_type` varchar(11) NOT NULL,
  `source_info` text,
  `metadata` text,
  `status` enum('completed','pending','refunded','initiated','canceled') NOT NULL DEFAULT 'initiated',
  `return_url` text,
  `webhook_url` text,
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pp_webhook_log`
--

CREATE TABLE `pp_webhook_log` (
  `id` int(11) NOT NULL,
  `ref` varchar(15) NOT NULL,
  `brand_id` varchar(15) NOT NULL,
  `payload` text NOT NULL,
  `url` text NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT '0',
  `response_body` text,
  `http_code` text,
  `status` enum('completed','pending','canceled') NOT NULL DEFAULT 'pending',
  `created_date` varchar(20) NOT NULL,
  `updated_date` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pp_addon`
--
ALTER TABLE `pp_addon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addon_id` (`addon_id`,`status`,`created_date`,`updated_date`);

--
-- Indexes for table `pp_addon_parameter`
--
ALTER TABLE `pp_addon_parameter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addon_id` (`addon_id`,`option_name`,`created_date`,`updated_date`);

--
-- Indexes for table `pp_admin`
--
ALTER TABLE `pp_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `a_id` (`a_id`,`email`),
  ADD KEY `username` (`username`),
  ADD KEY `created_date` (`created_date`,`updated_date`);

--
-- Indexes for table `pp_api`
--
ALTER TABLE `pp_api`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`,`api_key`,`created_date`,`updated_date`);

--
-- Indexes for table `pp_balance_verification`
--
ALTER TABLE `pp_balance_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`,`sender_key`,`type`,`created_date`,`updated_date`),
  ADD KEY `simslot` (`simslot`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `pp_brands`
--
ALTER TABLE `pp_brands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `created_date` (`created_date`,`updated_date`),
  ADD KEY `identify_name` (`identify_name`),
  ADD KEY `autoExchange` (`autoExchange`);

--
-- Indexes for table `pp_browser_log`
--
ALTER TABLE `pp_browser_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `a_id` (`a_id`,`cookie`,`created_date`,`updated_date`),
  ADD KEY `created_date` (`created_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `pp_currency`
--
ALTER TABLE `pp_currency`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`,`code`,`symbol`);

--
-- Indexes for table `pp_customer`
--
ALTER TABLE `pp_customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ref` (`ref`,`brand_id`,`email`,`mobile`),
  ADD KEY `created_date` (`created_date`,`updated_date`),
  ADD KEY `status` (`status`,`inserted_via`);

--
-- Indexes for table `pp_device`
--
ALTER TABLE `pp_device`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `created_date` (`created_date`,`updated_date`),
  ADD KEY `a_id` (`d_id`),
  ADD KEY `otp` (`otp`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `pp_domain`
--
ALTER TABLE `pp_domain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain` (`domain`),
  ADD KEY `created_date` (`created_date`,`updated_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `pp_env`
--
ALTER TABLE `pp_env`
  ADD PRIMARY KEY (`id`),
  ADD KEY `option_name` (`option_name`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `created_date` (`created_date`,`updated_date`);

--
-- Indexes for table `pp_faq`
--
ALTER TABLE `pp_faq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`,`created_date`,`updated_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `pp_gateways`
--
ALTER TABLE `pp_gateways`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`,`slug`),
  ADD KEY `g_id` (`gateway_id`),
  ADD KEY `created_date` (`created_date`,`updated_date`),
  ADD KEY `tab` (`tab`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `pp_gateways_parameter`
--
ALTER TABLE `pp_gateways_parameter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slug` (`gateway_id`,`option_name`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `created_date` (`created_date`,`updated_date`);

--
-- Indexes for table `pp_invoice`
--
ALTER TABLE `pp_invoice`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ref` (`ref`,`brand_id`),
  ADD KEY `created_date` (`created_date`,`updated_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `pp_invoice_items`
--
ALTER TABLE `pp_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `created_date` (`created_date`,`updated_date`);

--
-- Indexes for table `pp_payment_link`
--
ALTER TABLE `pp_payment_link`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ref` (`ref`,`brand_id`,`created_date`,`updated_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `pp_payment_link_field`
--
ALTER TABLE `pp_payment_link_field`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paymentLinkID` (`paymentLinkID`);

--
-- Indexes for table `pp_permission`
--
ALTER TABLE `pp_permission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`,`a_id`,`created_date`,`updated_date`);

--
-- Indexes for table `pp_sms_data`
--
ALTER TABLE `pp_sms_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`sender_key`,`amount`,`trx_id`),
  ADD KEY `created_date` (`created_date`,`updated_date`),
  ADD KEY `number` (`number`),
  ADD KEY `balance` (`balance`),
  ADD KEY `device_id_2` (`device_id`),
  ADD KEY `sender` (`sender`),
  ADD KEY `source` (`source`),
  ADD KEY `type` (`type`,`entry_type`,`edit_status`,`status`);

--
-- Indexes for table `pp_transaction`
--
ALTER TABLE `pp_transaction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`,`ref`,`trx_id`),
  ADD KEY `payment_method_id` (`gateway_id`,`sender_key`),
  ADD KEY `gateway_slug` (`sender_key`),
  ADD KEY `created_date` (`created_date`,`updated_date`),
  ADD KEY `sender` (`sender`),
  ADD KEY `source` (`source`,`status`),
  ADD KEY `sender_type` (`sender_type`);

--
-- Indexes for table `pp_webhook_log`
--
ALTER TABLE `pp_webhook_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ref` (`ref`),
  ADD KEY `brand_id` (`brand_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pp_addon`
--
ALTER TABLE `pp_addon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_addon_parameter`
--
ALTER TABLE `pp_addon_parameter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_admin`
--
ALTER TABLE `pp_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pp_api`
--
ALTER TABLE `pp_api`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_balance_verification`
--
ALTER TABLE `pp_balance_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_brands`
--
ALTER TABLE `pp_brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pp_browser_log`
--
ALTER TABLE `pp_browser_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pp_currency`
--
ALTER TABLE `pp_currency`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pp_customer`
--
ALTER TABLE `pp_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_device`
--
ALTER TABLE `pp_device`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_domain`
--
ALTER TABLE `pp_domain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_env`
--
ALTER TABLE `pp_env`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pp_faq`
--
ALTER TABLE `pp_faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_gateways`
--
ALTER TABLE `pp_gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pp_gateways_parameter`
--
ALTER TABLE `pp_gateways_parameter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_invoice`
--
ALTER TABLE `pp_invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_invoice_items`
--
ALTER TABLE `pp_invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_payment_link`
--
ALTER TABLE `pp_payment_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_payment_link_field`
--
ALTER TABLE `pp_payment_link_field`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_permission`
--
ALTER TABLE `pp_permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pp_sms_data`
--
ALTER TABLE `pp_sms_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_transaction`
--
ALTER TABLE `pp_transaction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pp_webhook_log`
--
ALTER TABLE `pp_webhook_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
