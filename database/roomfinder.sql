-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2018 at 02:49 PM
-- Server version: 10.1.32-MariaDB
-- PHP Version: 7.2.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `roomfinder`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED DEFAULT NULL,
  `jagga_id` int(10) UNSIGNED DEFAULT NULL,
  `image` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`id`, `room_id`, `jagga_id`, `image`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'aa3f57f66935784d73798661d249db6f.jpeg', '2018-06-06 03:52:40', '2018-06-06 03:52:40'),
(2, 1, NULL, '62d0da624640983c0c76e58a38baaeea.jpeg', '2018-06-06 03:52:40', '2018-06-06 03:52:40'),
(3, 2, NULL, 'ad46b3401d6fc8cdacc6816ad92cc1b1.jpeg', '2018-06-06 03:53:29', '2018-06-06 03:53:29'),
(4, 2, NULL, '3d4903fa8bb4ca946bca50f6f1983333.jpeg', '2018-06-06 03:53:29', '2018-06-06 03:53:29'),
(5, 2, NULL, '7a47e37c49329f47646436c69e92a84d.jpeg', '2018-06-06 03:53:29', '2018-06-06 03:53:29'),
(6, 2, NULL, '0ce39afc7d71bf2fb3c580e3b2756eaa.jpeg', '2018-06-06 03:53:29', '2018-06-06 03:53:29'),
(7, 2, NULL, '31fa2f8f335ef026c1360441747e8040.jpeg', '2018-06-06 03:53:29', '2018-06-06 03:53:29'),
(8, NULL, 1, '55bdb180eade01fb0cb2b3613a5293d6.jpeg', '2018-06-06 03:54:10', '2018-06-06 03:54:10'),
(9, NULL, 1, '136b9a71951feaa108b7a4b8a37d12e8.jpeg', '2018-06-06 03:54:10', '2018-06-06 03:54:10'),
(10, NULL, 2, '86decfc28d67b2f9eba162f5e64fa9ad.jpg', '2018-06-07 03:11:27', '2018-06-07 03:11:27'),
(11, NULL, 2, '22f05d6008cee6ac2bd0923e81d56887.jpg', '2018-06-07 03:11:27', '2018-06-07 03:11:27'),
(12, NULL, 2, 'ae670e520146a206d54ea305269b4cdf.jpg', '2018-06-07 03:11:27', '2018-06-07 03:11:27'),
(13, NULL, 3, '29b5acff74656a6d33295cbdb09696ec.jpg', '2018-06-07 03:11:41', '2018-06-07 03:11:41'),
(14, NULL, 3, '27a797e49440f1df9c66dd610d1385a3.jpg', '2018-06-07 03:11:41', '2018-06-07 03:11:41'),
(15, NULL, 3, '8f6d0cd02acb7915a9d95faa682101b3.jpg', '2018-06-07 03:11:41', '2018-06-07 03:11:41');

-- --------------------------------------------------------

--
-- Table structure for table `jaggas`
--

CREATE TABLE `jaggas` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loc_lat` double NOT NULL,
  `loc_lon` double NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sold` tinyint(1) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jaggas`
--

INSERT INTO `jaggas` (`id`, `user_id`, `type`, `phone_no`, `loc_lat`, `loc_lon`, `address`, `price`, `description`, `sold`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'area', '887756332', 3.56252, 6.236254, 'Banepa', 950000, 'Please click the name of the department you would like to reach for more information.', 0, '2018-06-07 06:53:42', '2018-06-06 03:54:09', '2016-03-07 18:15:00'),
(2, 2, 'land', '435634534435', 53.56252, 56.236254, 'Nagarkot', 950000, 'yekllow', 0, '2018-06-07 06:53:42', '2018-06-07 03:11:25', '2018-06-07 06:53:42'),
(3, 2, 'parking', '435634534435', 53.56252, 56.236254, 'Chabahil', 950000, 'blue', 0, '2018-06-07 06:53:42', '2018-06-07 03:11:39', '2018-06-07 06:53:42');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `loc_lat` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loc_lon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_id` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `loc_lat`, `loc_lon`, `device_type`, `device_id`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, NULL, 'ios', 'ba6c1bc5bea87497fb61c94fea5dcfd339392ff3e5b3387', '2018-06-06 03:51:34', '2018-06-06 03:51:34'),
(2, 3, NULL, NULL, 'android', 'ea87497fb61c94fea5dcfd339392ff3e5b3387', '2018-06-06 03:51:59', '2018-06-06 03:51:59'),
(3, 2, NULL, NULL, 'ios', 'ba6c1bc5bea87497fb61c94fea5dcfd339392ff3e5b3387', '2018-06-07 02:25:46', '2018-06-07 02:25:46'),
(4, 2, NULL, NULL, 'ios', 'ba6c1bc5bea87497fb61c94fea5dcfd339392ff3e5b3387', '2018-06-07 04:23:51', '2018-06-07 04:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2018_05_27_090144_create_user_sessions_table', 1),
(4, '2018_05_27_090212_create_logs_table', 1),
(5, '2018_05_27_093407_create_rooms_table', 1),
(6, '2018_05_27_093427_create_jaggas_table', 1),
(7, '2018_05_27_094801_create_feedback_table', 1),
(8, '2018_05_27_095639_create_spam_table', 1),
(9, '2018_05_27_175608_create_user_favourites_table', 1),
(10, '2018_05_27_183204_create_images__table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mobile_target_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `jagga_id` int(11) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `is_read` enum('0','1') NOT NULL DEFAULT '0',
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `message` text,
  `content_link` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `mobile_target_id`, `room_id`, `jagga_id`, `type`, `is_read`, `deleted_at`, `created_at`, `updated_at`, `message`, `content_link`) VALUES
(1, 3, 3, NULL, NULL, 'notify_owner', '0', '2018-06-07 12:33:32', '2018-06-07 06:48:32', '2018-06-07 06:48:32', 'Please reactivate this post.', NULL),
(2, 3, 3, NULL, NULL, 'notify_owner', '0', '2018-06-07 12:33:32', '2018-06-07 06:48:32', '2018-06-07 06:48:32', 'Please reactivate this post.', NULL),
(3, 3, 3, NULL, NULL, 'notify_owner', '0', '2018-06-07 12:43:00', '2018-06-07 06:53:42', '2017-03-07 06:53:42', 'Please reactivate this post.', NULL),
(4, 2, 2, NULL, NULL, 'notify_owner', '0', '2018-06-07 12:38:42', '2018-06-07 06:53:42', '2018-06-07 06:53:42', 'Please reactivate this post.', NULL),
(5, 2, 2, NULL, NULL, 'notify_owner', '0', '2018-06-07 12:38:42', '2018-06-07 06:53:42', '2018-06-07 06:53:42', 'Please reactivate this post.', NULL),
(6, 3, 3, NULL, NULL, 'notify_owner', '0', '2018-06-07 12:43:44', '2018-06-07 06:58:44', '2018-06-07 06:58:44', 'Please reactivate this post.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_of_floor` int(11) NOT NULL,
  `no_of_room` int(11) NOT NULL,
  `parking` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kitchen` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `restroom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loc_lat` double NOT NULL,
  `loc_lon` double NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preference` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `occupied` tinyint(1) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `user_id`, `type`, `no_of_floor`, `no_of_room`, `parking`, `kitchen`, `restroom`, `phone_no`, `loc_lat`, `loc_lon`, `address`, `preference`, `price`, `description`, `occupied`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'flat', 5, 12, 'yes', '4', '5', '9810563252', 23.563289, 63.2536254, 'Naxal', 'excellent', 1800000, 'The pyramid at Luxor Resort in Las Vegas, with its beam of light', 0, '2018-06-07 06:48:32', '2018-04-05 18:15:00', '2018-06-07 06:48:32'),
(2, 3, 'house', 6, 12, 'yes', '4', '5', '9841523652', 2.563289, 3.2536254, 'Kusunti', 'excellent', 1800000, 'The s, with its beam of light jerry', 0, '2018-06-07 06:48:32', '2018-06-06 03:53:27', '2018-06-07 06:48:32');

-- --------------------------------------------------------

--
-- Table structure for table `spam`
--

CREATE TABLE `spam` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `complains` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_id` int(10) UNSIGNED DEFAULT NULL,
  `jagga_id` int(10) UNSIGNED DEFAULT NULL,
  `read` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userfavourites`
--

CREATE TABLE `userfavourites` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED DEFAULT NULL,
  `jagga_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `userfavourites`
--

INSERT INTO `userfavourites` (`id`, `user_id`, `room_id`, `jagga_id`, `created_at`, `updated_at`) VALUES
(2, 2, NULL, NULL, '2018-06-20 18:15:00', NULL),
(4, 2, NULL, NULL, '2018-06-29 18:15:00', NULL),
(5, 2, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_pic` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `profile_pic`, `email`, `password`, `remember_token`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Manish', NULL, 'admin@roomfinder.com', '$2y$10$Zl9jxdoGPmyUzxlDjfQIeura7RrILu6w84TtaV.aV/DHwbjrWh5U.', NULL, 'active', NULL, '2018-06-06 03:49:03', '2018-06-06 03:49:03'),
(2, NULL, '74572ed550d843bcfc377828161f3485.jpg', 'batman@yk20.com', NULL, NULL, 'active', NULL, '2018-06-06 03:51:33', '2018-06-07 04:23:51'),
(3, NULL, '96d0c7c4c73d37cca7394a202ad63ca7.jpeg', 'superman@yk20.com', NULL, NULL, 'active', NULL, '2018-06-06 03:51:58', '2018-06-06 03:51:59');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_id` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fb_device_token` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `device_type`, `device_id`, `access_token`, `fb_device_token`, `created_at`, `updated_at`) VALUES
(1, 2, 'ios', 'ba6c1bc5bea87497fb61c94fea5dcfd339392ff3e5b3387', '28410eb0646b92f045966cc58ebaa11d', 'GD-enuzMwEyhkX1Fr9BYqi6TYGlRtgO-1D8yw7SMS3JqlS2UdH39hA7p5GoxxcFpTXvap2v83eeJgl6I9uW25F6Q6hgJmOO9omBLNe9x3lsiTpSceB-y4ittx6w-nqVyjy_ZZ9', '2018-06-06 03:51:34', '2018-06-07 04:23:51'),
(2, 3, 'android', 'ea87497fb61c94fea5dcfd339392ff3e5b3387', '748f11d6673eefc471e34c3c81752142', 'kX1Fr9BYqi6TYGlRtgO-1D8yw7SMS3JqlS2UdH39hA7p5GoxxcFpTXvap2v83eeJgl6I9uW25F6Q6hgJmOO9omBLNe9x3lsiTpSceB-y4ittx6w-nqVyjy_ZZ9', '2018-06-06 03:51:59', '2018-06-06 03:51:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_user_id_foreign` (`user_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `images_room_id_foreign` (`room_id`),
  ADD KEY `images_jagga_id_foreign` (`jagga_id`);

--
-- Indexes for table `jaggas`
--
ALTER TABLE `jaggas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jaggas_user_id_foreign` (`user_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rooms_user_id_foreign` (`user_id`);

--
-- Indexes for table `spam`
--
ALTER TABLE `spam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spam_user_id_foreign` (`user_id`),
  ADD KEY `spam_room_id_foreign` (`room_id`),
  ADD KEY `spam_jagga_id_foreign` (`jagga_id`);

--
-- Indexes for table `userfavourites`
--
ALTER TABLE `userfavourites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userfavourites_user_id_foreign` (`user_id`),
  ADD KEY `userfavourites_room_id_foreign` (`room_id`),
  ADD KEY `userfavourites_jagga_id_foreign` (`jagga_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_sessions_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `jaggas`
--
ALTER TABLE `jaggas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `spam`
--
ALTER TABLE `spam`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `userfavourites`
--
ALTER TABLE `userfavourites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_jagga_id_foreign` FOREIGN KEY (`jagga_id`) REFERENCES `jaggas` (`id`),
  ADD CONSTRAINT `images_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `jaggas`
--
ALTER TABLE `jaggas`
  ADD CONSTRAINT `jaggas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `spam`
--
ALTER TABLE `spam`
  ADD CONSTRAINT `spam_jagga_id_foreign` FOREIGN KEY (`jagga_id`) REFERENCES `jaggas` (`id`),
  ADD CONSTRAINT `spam_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `spam_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `userfavourites`
--
ALTER TABLE `userfavourites`
  ADD CONSTRAINT `userfavourites_jagga_id_foreign` FOREIGN KEY (`jagga_id`) REFERENCES `jaggas` (`id`),
  ADD CONSTRAINT `userfavourites_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `userfavourites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
