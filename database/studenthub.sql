-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2026 at 05:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studenthub`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `audit_id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `target_type` varchar(30) NOT NULL,
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`audit_id`, `actor_id`, `action`, `target_type`, `target_id`, `description`, `created_at`) VALUES
(1, 3, 'create', 'event', 5, 'Created event.', '2026-10-28 04:13:06'),
(2, 3, 'update', 'event', 5, 'Updated event details.', '2026-10-28 04:13:19'),
(3, 3, 'delete', 'event', 5, 'Deleted event.', '2026-10-28 04:13:39'),
(4, 3, 'create', 'event', 6, 'Created event through JSON API.', '2026-10-28 04:16:03'),
(5, 3, 'update', 'event', 6, 'Updated event through JSON API.', '2026-10-28 04:16:14'),
(6, 3, 'delete', 'event', 6, 'Deleted event through JSON API.', '2026-10-28 04:16:26'),
(7, 3, 'create', 'student', 25, 'Created student account.', '2026-10-28 04:21:22'),
(8, 3, 'update', 'student', 25, 'Updated student account.', '2026-10-28 04:21:37'),
(9, 3, 'delete', 'student', 25, 'Deleted student account.', '2026-10-28 04:21:41');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(10) UNSIGNED NOT NULL,
  `event_name` varchar(150) NOT NULL,
  `event_date` date NOT NULL,
  `venue` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `poster_filename` varchar(100) DEFAULT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `event_date`, `venue`, `category`, `poster_filename`, `status`) VALUES
(1, 'Web Development Workshop', '2026-11-10', 'CSPIT Computer Lab', 'Technical', NULL, 'open');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `student_id`, `event_id`, `registered_at`) VALUES
(1, 1, 1, '2026-10-22 12:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(254) NOT NULL,
  `mobile` varchar(10) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `course` varchar(10) NOT NULL,
  `year` tinyint(3) UNSIGNED NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `full_name`, `email`, `mobile`, `password_hash`, `course`, `year`, `gender`, `created_at`, `role`, `last_login`) VALUES
(1, 'Tej Patel', 'tej@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'AIML', 2, 'Male', '2026-10-22 12:14:17', 'student', NULL),
(2, 'Tej Patel', 'tej.practical9@example.com', '9876543210', '$2y$10$8uUCqwEgFz0ZcVCCacLMuObKcwUH3sS9WDprv7ITGd7qJG0.5W/V2', 'AIML', 2, 'Male', '2026-10-25 05:19:20', 'student', '2026-10-25 12:06:46'),
(3, 'Het', 'admin.studenthub@example.com', '9876543210', '$2y$10$icx6afb61MsDIcFmnspJruYpsKM6kGZbYLv1eK54k9iW4.wf66vFK', 'AIML', 1, 'Male', '2026-10-25 06:40:42', 'admin', '2026-10-28 10:13:39'),
(5, 'Aarav Patel', 'sample.student1@example.com', '9000000001', '$2y$10$3mpNpemoVdQkjdwp2M6Zau24/u8ovgFCKct6LxmlEMbQKxQY5JrIa', 'AIML', 1, 'Male', '2026-10-25 07:39:58', 'student', NULL),
(6, 'Diya Shah', 'sample.student2@example.com', '9000000002', '$2y$10$oroD9vTJt87uxden8faGVe//MvDO8PffYdZv8lm4aidYUhsPsH2ri', 'CE', 2, 'Female', '2026-10-25 07:39:58', 'student', NULL),
(7, 'Vivaan Mehta', 'sample.student3@example.com', '9000000003', '$2y$10$AGD/RWbFhS8bs.abWt9TD.kq58.wUo/g.WT6aty0k8JJBrwKGteie', 'IT', 3, 'Male', '2026-10-25 07:39:58', 'student', NULL),
(8, 'Ananya Desai', 'sample.student4@example.com', '9000000004', '$2y$10$woYY0Qr98HZLcqyfBA2qYewDJCfh98aFytWyrFVzxIzwHdLiJp1X.', 'AIML', 4, 'Female', '2026-10-25 07:39:58', 'student', NULL),
(9, 'Aditya Joshi', 'sample.student5@example.com', '9000000005', '$2y$10$IRLOIy4u5YL66yRwYiLJleHouP2YYiGPhmWD6tph7kiqhN32gvbDO', 'CE', 1, 'Male', '2026-10-25 07:39:58', 'student', NULL),
(10, 'Isha Patel', 'sample.student6@example.com', '9000000006', '$2y$10$tP5HMgDGS.PkQKrasnueE.wuM8uBO7V4oR9AMoVYVlgdi6wJN8/u2', 'IT', 2, 'Female', '2026-10-25 07:39:58', 'student', NULL),
(11, 'Arjun Shah', 'sample.student7@example.com', '9000000007', '$2y$10$h5.MaWjvPiuQiAfVeNcWb.1aLs7Lav9HJ9pxoyAM7k8j1lXtHrs2m', 'AIML', 3, 'Male', '2026-10-25 07:39:58', 'student', NULL),
(12, 'Kavya Mehta', 'sample.student8@example.com', '9000000008', '$2y$10$/rjggFJMQgniGW8f5/88X.4xHNcLT4wkUekG1RLEvhbnRfqdjFBwm', 'CE', 4, 'Female', '2026-10-25 07:39:58', 'student', NULL),
(13, 'Krish Desai', 'sample.student9@example.com', '9000000009', '$2y$10$0hDrN00Tr.A5rDBenrJyi.Z9ZGGoA1olOBEy.3v/wMDKE.8D9scgi', 'IT', 1, 'Male', '2026-10-25 07:39:58', 'student', NULL),
(14, 'Riya Joshi', 'sample.student10@example.com', '9000000010', '$2y$10$2wynKUw/b1mpHapuZopW9OIiwRZLJOg2aT7zr4U2NoDUXz8S3PHfa', 'AIML', 2, 'Female', '2026-10-25 07:39:58', 'student', NULL),
(15, 'Dev Patel', 'sample.student11@example.com', '9000000011', '$2y$10$0k2eesjtObdyJu1rQOEDdeDSJWp3GDTJj6C8zddnkvXRiN9dTUtj2', 'CE', 3, 'Male', '2026-10-25 07:39:59', 'student', NULL),
(16, 'Mira Shah', 'sample.student12@example.com', '9000000012', '$2y$10$do77PzQ7/DOpdZxUn/j7/OaEQd0sZNdKQDjnqTS6u.ZxmXcFRMV5.', 'IT', 4, 'Female', '2026-10-25 07:39:59', 'student', NULL),
(17, 'Dhruv Mehta', 'sample.student13@example.com', '9000000013', '$2y$10$1xMboGe4/QCdvbQoDRTBM.1rJZt/NRXb0cdFaOXVzUD/oyi8FoQk6', 'AIML', 1, 'Male', '2026-10-25 07:39:59', 'student', NULL),
(18, 'Nisha Desai', 'sample.student14@example.com', '9000000014', '$2y$10$L6GHn5vnyw.EOm13.6ar1uMViV1yby/Evx8KeYgkXADmdHGzdpACS', 'CE', 2, 'Female', '2026-10-25 07:39:59', 'student', NULL),
(19, 'Om Joshi', 'sample.student15@example.com', '9000000015', '$2y$10$Q8Vho009aZCqy3CJ.p4w/eAL0jCE/LgXKYdO/qLAQuma5JkS4uo8W', 'IT', 3, 'Male', '2026-10-25 07:39:59', 'student', NULL),
(20, 'Pooja Patel', 'sample.student16@example.com', '9000000016', '$2y$10$P9WA5YOxdNKFj5zB6KRp5.qxLOFQgufai5BfP5Lw3LIwoAgM4wkNy', 'AIML', 4, 'Female', '2026-10-25 07:39:59', 'student', NULL),
(21, 'Yash Shah', 'sample.student17@example.com', '9000000017', '$2y$10$QH7PK/EdXBBfZ9heMV9GJOBmOlmtJJvXN47EkhL7kLMDFCoV3OXPG', 'CE', 1, 'Male', '2026-10-25 07:39:59', 'student', NULL),
(22, 'Sara Mehta', 'sample.student18@example.com', '9000000018', '$2y$10$INIkVES089nolL5OfIuNHu19xI53A1.ufYNwbny6j8ggoiiMHI/X.', 'IT', 2, 'Female', '2026-10-25 07:39:59', 'student', NULL),
(23, 'Neel Desai', 'sample.student19@example.com', '9000000019', '$2y$10$dKfspObWEXC206dqLIm/1uGP4oslVRnzkD5eRguSSYdGc1fTYO4/e', 'AIML', 3, 'Male', '2026-10-25 07:39:59', 'student', NULL),
(24, 'Tara Joshi', 'sample.student20@example.com', '9000000020', '$2y$10$LZXBVtP8xhHFRhoVGkvWO.4RomhrUppUepCwVQr0/MT6/.aYARJha', 'CE', 4, 'Female', '2026-10-25 07:39:59', 'student', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `idx_audit_created` (`created_at`),
  ADD KEY `idx_audit_actor` (`actor_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `unique_student_event` (`student_id`,`event_id`),
  ADD KEY `fk_registration_event` (`event_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_actor` FOREIGN KEY (`actor_id`) REFERENCES `students` (`student_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `fk_registration_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_registration_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
