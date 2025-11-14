-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2024 at 03:21 PM
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
-- Database: `organ_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `organ_name` varchar(255) NOT NULL,
  `blood_type` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `organ_name`, `blood_type`, `message`, `created_at`, `updated_at`) VALUES
(16, 3, 'Kidney', 'B+', 'I am in need of a kidney transplant due to my deteriorating condition.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(17, 4, 'Liver', 'AB-', 'Urgent liver donation needed for survival.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(18, 5, 'Heart', 'O+', 'Heart transplant required for my father, who is in critical condition.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(19, 6, 'Lung', 'A-', 'Looking for a lung donor due to severe respiratory failure.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(20, 7, 'Pancreas', 'B-', 'Pancreas donation is urgently needed for my sister.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(21, 8, 'Kidney', 'AB+', 'Need a kidney transplant for my mother.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(22, 9, 'Liver', 'O-', 'Liver transplant required as part of my ongoing treatment.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(23, 10, 'Heart', 'A+', 'Heart donor urgently required for my sibling.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(24, 11, 'Corneas', 'B+', 'I need a cornea transplant to restore my vision.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(25, 12, 'Bone Marrow', 'O+', 'Bone marrow donation urgently required for my cousin.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(26, 13, 'Skin', 'A-', 'Skin graft donor needed after suffering severe burns.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(27, 14, 'Lung', 'O+', 'I am seeking a lung transplant due to chronic lung disease.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(28, 15, 'Pancreas', 'A-', 'My friend is in need of a pancreas transplant as soon as possible.', '2024-10-23 19:44:56', '2024-10-23 19:44:56'),
(29, 3, 'Liver', 'B-', 'Hello', '2024-10-29 08:41:19', '2024-10-29 08:41:19'),
(30, 3, 'Heart', 'AB+', 'Helli', '2024-10-29 08:43:27', '2024-10-29 08:43:27'),
(31, 3, 'Heart', 'B-', 'hjhjdasd', '2024-10-29 08:45:14', '2024-10-29 08:45:14'),
(32, 3, 'Pancreas', 'AB+', 'Helllo', '2024-10-29 08:47:59', '2024-10-29 08:47:59'),
(33, 3, 'Small Intestine', 'O+', 'Hello', '2024-10-29 08:49:55', '2024-10-29 08:49:55'),
(34, 3, 'Corneas', 'O+', 'dsadsadsad', '2024-10-29 08:50:04', '2024-10-29 08:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(4, '2024_10_06_134906_create_settings_table', 2),
(5, '2024_10_06_153649_create_students_table', 2),
(6, '2024_10_06_153918_create_students_table', 3),
(20, '0001_01_01_000000_create_users_table', 4),
(21, '0001_01_01_000001_create_cache_table', 4),
(22, '0001_01_01_000002_create_jobs_table', 4),
(23, '2024_10_06_154355_create_students_table', 4),
(24, '2024_10_06_155407_create_courses_table', 4),
(25, '2024_10_06_155731_create_batches_table', 4),
(26, '2024_10_06_160340_create_enrollments_table', 4),
(27, '2024_10_23_111740_create_organs_table', 5),
(28, '2024_10_23_162400_create_mobile_users_table', 6),
(30, '2024_10_23_174527_create_organ_requests_table', 7),
(31, '2024_10_23_193911_create_messages_table', 8),
(32, '2024_10_27_102227_create_personal_access_tokens_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table `mobile_users`
--

CREATE TABLE `mobile_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `dob` date NOT NULL,
  `blood_type` varchar(255) DEFAULT NULL,
  `organ` varchar(255) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `status` enum('pending','approved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mobile_users`
--

INSERT INTO `mobile_users` (`id`, `email`, `full_name`, `password`, `phone_number`, `gender`, `dob`, `blood_type`, `organ`, `allergies`, `status`, `created_at`, `updated_at`) VALUES
(3, 'mjohnson@example.com', 'Michael Johnson', 'guna1234', '5678901234', 'male', '1992-10-05', 'B+', 'Heart', 'Shellfish', 'approved', '2024-10-23 16:26:26', '2024-10-29 01:50:47'),
(4, 'edavis@example.com', 'Emily Davis', 'password123', '4321098765', 'female', '1995-03-19', 'AB-', 'Lung', 'Penicillin', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(5, 'rmiller@example.com', 'Robert Miller', 'password123', '6789012345', 'male', '1988-07-12', 'O+', 'Pancreas', 'None', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(6, 'swilson@example.com', 'Sophia Wilson', 'password123', '3456789012', 'female', '1993-12-07', 'A-', 'Kidney', 'Dust', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(7, 'jmoore@example.com', 'James Moore', 'password123', '9012345678', 'male', '1986-09-21', 'B-', 'Liver', 'Latex', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(8, 'itaylor@example.com', 'Isabella Taylor', 'password123', '7654321098', 'female', '1991-02-14', 'AB+', 'Heart', 'Cats', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(9, 'wanderson@example.com', 'William Anderson', 'password123', '8901234567', 'male', '1989-08-30', 'O-', 'Lung', 'None', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(10, 'othomas@example.com', 'Olivia Thomas', 'password123', '2345678901', 'female', '1994-11-03', 'A+', 'Kidney', 'Gluten', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(11, 'hwhite@example.com', 'Henry White', 'password123', '9876543210', 'male', '1987-06-25', 'B+', 'Pancreas', 'Pollen', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(12, 'mharris@example.com', 'Mia Harris', 'password123', '8765432109', 'female', '1992-01-05', 'O+', 'Liver', 'Soy', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(13, 'emartin@example.com', 'Ethan Martin', 'password123', '6543210987', 'male', '1990-04-18', 'A-', 'Kidney', 'Milk', 'pending', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(14, 'ajackson@example.com', 'Amelia Jackson', 'password123', '5432109876', 'female', '1996-07-09', 'B-', 'Heart', 'Eggs', 'approved', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(15, 'dlee@example.com', 'Daniel Lee', 'password123', '3210987654', 'male', '1993-03-24', 'O-', 'Lung', 'None', 'approved', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(16, 'gallen@example.com', 'Grace Allen', 'password123', '1098765432', 'female', '1985-12-17', 'AB+', 'Pancreas', 'Peanuts', 'approved', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(17, 'ascott@example.com', 'Alexander Scott', 'password123', '4567890123', 'male', '1991-10-12', 'A+', 'Liver', 'None', 'approved', '2024-10-23 16:26:26', '2024-10-23 16:26:26'),
(21, 'gunarakulan@gmail.com', 'gunarakulan', 'guna12345678', '0740001141', 'male', '1997-11-01', 'B-', 'Corneas', 'nononono', 'pending', '2024-10-26 07:51:18', '2024-10-26 07:51:18'),
(23, 'gunarakulan@gmail.comsss', 'gunarakulan', 'guna12345678', '0740001141', 'male', '1997-11-01', 'B-', 'Corneas', 'nononono', 'pending', '2024-10-26 07:59:27', '2024-10-26 07:59:27'),
(24, 'gunarakulan@gmail.comsssdddjjj', 'gunarakulan', 'guna12345678', '0740001141', 'male', '1997-11-01', 'B-', 'Corneas', 'nononono', 'pending', '2024-10-26 08:03:48', '2024-10-26 08:03:48'),
(25, 'gunarakulan@gmail.comsssdddjjjjj', 'gunarakulan', 'guna12345678', '0740001141', 'male', '1997-11-01', 'B-', 'Corneas', 'nononono', 'pending', '2024-10-26 08:04:11', '2024-10-26 08:04:11'),
(26, 'gunarakulan@gmail.comsssdddjjjjjdsds', 'gunarakulan', 'guna12345678', '0740001141', 'male', '1997-11-01', 'B-', 'Corneas', 'nononono', 'pending', '2024-10-26 08:42:26', '2024-10-26 08:42:26'),
(27, 'gunarakulan@gmail.comdsfds', 'hahhaha', 'guna1234', '0740001141', 'male', '1997-11-01', 'A+', 'Pancreas', 'nonono', 'pending', '2024-10-26 08:47:02', '2024-10-26 08:47:02'),
(28, 'gunaraku@mail.com', 'Gunarakulan', 'fghhjjjjjjj34532', '123452425', 'male', '1997-11-11', 'B-', 'Corneas', 'nnono', 'pending', '2024-10-26 10:26:35', '2024-10-26 10:26:35'),
(29, 'guna@gmail.com', 'guafd', 'guna12345678', '123456789', 'female', '1997-08-11', 'A+', 'Lung', 'nononono', 'pending', '2024-10-26 11:13:53', '2024-10-26 11:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `organs`
--

CREATE TABLE `organs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organ_name` varchar(255) NOT NULL,
  `blood_type` varchar(255) NOT NULL,
  `donor_name` varchar(255) NOT NULL,
  `donor_age` int(11) NOT NULL,
  `donor_gender` enum('male','female','other') NOT NULL,
  `organ_type` varchar(255) NOT NULL,
  `organ_condition` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organs`
--

INSERT INTO `organs` (`id`, `organ_name`, `blood_type`, `donor_name`, `donor_age`, `donor_gender`, `organ_type`, `organ_condition`, `created_at`, `updated_at`) VALUES
(4, 'Kidney', 'O+', 'John Doe', 45, 'male', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(5, 'Heart', 'B+', 'Jane Smith', 29, 'female', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(6, 'Liver', 'B+', 'Mark Johnson', 50, 'male', 'Vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(7, 'Lung', 'AB-', 'Emily Davis', 36, 'female', 'Vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(8, 'Pancreas', 'O-', 'Robert Wilson', 48, 'male', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(9, 'Cornea', 'A+', 'Sarah Lee', 22, 'female', 'Non-vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(10, 'Bone Marrow', 'B-', 'Michael Brown', 40, 'male', 'Non-vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(11, 'Kidney', 'O-', 'Laura White', 31, 'female', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(12, 'Heart', 'AB+', 'David Thompson', 60, 'male', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(13, 'Liver', 'A+', 'Jessica Green', 55, 'female', 'Vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(14, 'Pancreas', 'A-', 'Sarah Lee', 25, 'female', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(15, 'Bone Marrow', 'B+', 'Michael Brown', 55, 'male', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(16, 'Skin', 'O+', 'Emily Davis', 49, 'male', 'Non-vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(17, 'Lung', 'A+', 'Michael Brown', 57, 'male', 'Vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(18, 'Small Intestine', 'O+', 'Laura White', 57, 'female', 'Vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(19, 'Corneas', 'A-', 'John Doe', 28, 'male', 'Non-vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(20, 'Liver', 'AB+', 'Sarah Lee', 52, 'female', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(21, 'Heart', 'O-', 'David Thompson', 34, 'male', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(22, 'Kidney', 'A-', 'Laura White', 38, 'female', 'Vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(23, 'Pancreas', 'O-', 'Jane Smith', 36, 'female', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(24, 'Liver', 'B+', 'Emily Davis', 48, 'female', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(25, 'Heart Valves', 'AB-', 'Mark Johnson', 49, 'male', 'Vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(26, 'Corneas', 'O+', 'Robert Wilson', 37, 'male', 'Non-vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(27, 'Heart', 'B-', 'Jessica Green', 56, 'female', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(28, 'Bone Marrow', 'O-', 'Michael Brown', 42, 'male', 'Vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(29, 'Kidney', 'AB+', 'Sarah Lee', 29, 'female', 'Vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(30, 'Liver', 'A+', 'Mark Johnson', 53, 'male', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(31, 'Lung', 'AB-', 'John Doe', 38, 'male', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(32, 'Pancreas', 'A-', 'Jane Smith', 44, 'female', 'Vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(33, 'Small Intestine', 'B+', 'David Thompson', 54, 'male', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(34, 'Heart Valves', 'O+', 'Jessica Green', 48, 'female', 'Vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(35, 'Corneas', 'A-', 'Laura White', 35, 'female', 'Non-vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(36, 'Skin', 'B-', 'Mark Johnson', 43, 'male', 'Non-vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(37, 'Kidney', 'O+', 'Robert Wilson', 50, 'male', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(38, 'Heart', 'AB-', 'Emily Davis', 28, 'female', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(39, 'Bone Marrow', 'B+', 'Michael Brown', 51, 'male', 'Vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(40, 'Liver', 'A+', 'Sarah Lee', 59, 'female', 'Vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(41, 'Pancreas', 'B-', 'John Doe', 30, 'male', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(42, 'Small Intestine', 'A-', 'Jessica Green', 34, 'female', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(43, 'Corneas', 'O+', 'David Thompson', 52, 'male', 'Non-vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(44, 'Lung', 'AB+', 'Laura White', 44, 'female', 'Vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(45, 'Kidney', 'O-', 'Emily Davis', 35, 'female', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(46, 'Skin', 'B-', 'Jane Smith', 41, 'female', 'Non-vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(47, 'Heart Valves', 'A+', 'John Doe', 55, 'male', 'Vital', 'Healthy', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(48, 'Liver', 'B-', 'Robert Wilson', 32, 'male', 'Vital', 'Good', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(49, 'Pancreas', 'AB-', 'Sarah Lee', 40, 'female', 'Vital', 'Moderate', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(50, 'Bone Marrow', 'O-', 'Mark Johnson', 53, 'male', 'Vital', 'Critical', '2024-10-23 17:49:27', '2024-10-23 17:49:27'),
(51, 'Heart', 'O+', 'John Doe', 45, 'male', 'Vital', 'Healthy', '2024-10-23 19:10:43', '2024-10-23 19:10:43'),
(52, 'Liver', 'A-', 'Jane Smith', 38, 'female', 'Vital', 'Moderate', '2024-10-23 19:10:43', '2024-10-23 19:10:43'),
(53, 'Kidney', 'B+', 'Mark Johnson', 50, 'male', 'Vital', 'Critical', '2024-10-23 19:10:43', '2024-10-23 19:10:43'),
(54, 'Lung', 'AB+', 'Emily Davis', 29, 'female', 'Vital', 'Good', '2024-10-23 19:10:43', '2024-10-23 19:10:43'),
(55, 'Pancreas', 'O-', 'Robert Brown', 60, 'male', 'Vital', 'Healthy', '2024-10-23 19:10:43', '2024-10-23 19:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `organ_requests`
--

CREATE TABLE `organ_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organ_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organ_requests`
--

INSERT INTO `organ_requests` (`id`, `organ_id`, `user_id`, `date`, `status`, `created_at`, `updated_at`) VALUES
(31, 5, 3, '2024-10-29', 'pending', '2024-10-29 08:50:33', '2024-10-29 08:50:33');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3eXwsoZPqCRJpt9sTFI9keLZgnsa0rV0RtWahGxP', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQ1pNdk9tVmhZSklIMVdRRHhiWVJsRzVzU2lLTEJpQWlOVjRSc1BpciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozMDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3NldHRpbmdzIjt9fQ==', 1730191452),
('6xl7BkEsEz6uBOV8XFqNPwFx4jGAm3sNCEareQTV', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSzA1UzlUYktHVDRWRVlqc2lQZnljaDdTN0hoNnZidVlsQTJ4dld5WiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1729714887),
('AeQDxCDzopaObgBkYVqbn7xmdytMJczZ53PSn7lE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNENEUTZ6NUxUaXNjcVVlWmJGbkNhNmRndmEwVTJ2TlhkV3ZZR3Z5WCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1729713451),
('km2a1fKQOyw0l0RyZYwom8rFLnr9t4y1nJSxmGh5', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibldPWHRENHhDaWJxQ3VkNTFyelFORVJ5Wm1sNzh2eHFuTmpVWUhOQiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fX0=', 1730049919),
('PtTJiTn5zoQOomjADXoZW8LXacPTpnnY3HxOWhEO', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieHJkaFBPM3hleERMVjV3UVJ2UkhKaE5pR2cxcGQycmwwczJZcXN3USI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3VzZXJzL21vYmlsZSI7fX0=', 1729952779),
('QrJlOO3GKLHlgARuSZzVZPipFlin5sszSfLKm3im', NULL, '127.0.0.1', 'Dart/3.5 (dart:io)', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiTmU3WEw3dlR3akVyclJyS0NYRzRwWmNJOHlmNFlmQkVySFVGTDVOMyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1729945442),
('sRgSgWLEt53kwO4Rv6UuweoXivBQ7pTHoZGGRyIu', NULL, '127.0.0.1', 'Dart/3.5 (dart:io)', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiRGc3UFk2b2d5TnZ2eURQWGJwNXFVajY3Z0ZEQVo4bTV4THdRTXhlUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1729945428),
('TdQU6dHqtGCddGIhPWLEfUkScoJDBT22YWLi1rbl', NULL, '127.0.0.1', 'Dart/3.5 (dart:io)', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoib3BaOEg3UUo4dTRNWERjUlVIYlZ6dEp5T1NCQ0VTcURwa2ZqN1UwUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1729944947),
('VYBZG3MxADLP0ASaih7HTpmImJQdofbKtPaqEt95', NULL, '127.0.0.1', 'Dart/3.5 (dart:io)', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiS21kNGtsWmdMUzZGb29GWFR2dnlhTUhHRVZsZmRFanhndFhVVmtpRCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1729944517);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$12$u1VCsfyqZ8v1Ae9kNlPDSeXX8Yu5n5BY8vbmaNGC14m7QAcEqO8pm', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_users`
--
ALTER TABLE `mobile_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile_users_email_unique` (`email`);

--
-- Indexes for table `organs`
--
ALTER TABLE `organs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `organ_requests`
--
ALTER TABLE `organ_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organ_requests_organ_id_foreign` (`organ_id`),
  ADD KEY `organ_requests_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `mobile_users`
--
ALTER TABLE `mobile_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `organs`
--
ALTER TABLE `organs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `organ_requests`
--
ALTER TABLE `organ_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `mobile_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `organ_requests`
--
ALTER TABLE `organ_requests`
  ADD CONSTRAINT `organ_requests_organ_id_foreign` FOREIGN KEY (`organ_id`) REFERENCES `organs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organ_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `mobile_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
