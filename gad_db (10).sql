-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 17, 2025 at 05:19 AM
-- Server version: 8.0.31
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gad_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_rank`
--

DROP TABLE IF EXISTS `academic_rank`;
CREATE TABLE IF NOT EXISTS `academic_rank` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rank_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `academic_rank`
--

INSERT INTO `academic_rank` (`id`, `rank_name`) VALUES
(1, 'Professor'),
(2, 'Associate Professor'),
(3, 'Assistant Professor');

-- --------------------------------------------------------

--
-- Table structure for table `academic_ranks`
--

DROP TABLE IF EXISTS `academic_ranks`;
CREATE TABLE IF NOT EXISTS `academic_ranks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `academic_rank` varchar(100) NOT NULL,
  `salary_grade` int NOT NULL,
  `monthly_salary` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) GENERATED ALWAYS AS ((`monthly_salary` / 176)) STORED,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `academic_ranks`
--

INSERT INTO `academic_ranks` (`id`, `academic_rank`, `salary_grade`, `monthly_salary`) VALUES
(84, 'Instructor II', 9, '35000.00'),
(85, 'Instructor III', 10, '43000.00'),
(110, 'Instructor I', 8, '31000.00');

-- --------------------------------------------------------

--
-- Table structure for table `credentials`
--

DROP TABLE IF EXISTS `credentials`;
CREATE TABLE IF NOT EXISTS `credentials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `credentials`
--

INSERT INTO `credentials` (`id`, `username`, `password`) VALUES
(1, 'Lipa', 'lipa'),
(2, 'Pablo Borbon', 'pablo borbon'),
(3, 'Alangilan', 'alangilan'),
(4, 'Nasugbu', 'nasugbu'),
(5, 'Malvar', 'malvar'),
(6, 'Rosario', 'rosario'),
(7, 'Balayan', 'balayan'),
(8, 'Lemery', 'lemery'),
(9, 'San Juan', 'san juan'),
(10, 'Lobo', 'lobo'),
(11, 'Central', 'central');

-- --------------------------------------------------------

--
-- Table structure for table `gpb_entries`
--

DROP TABLE IF EXISTS `gpb_entries`;
CREATE TABLE IF NOT EXISTS `gpb_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `gender_issue` text NOT NULL,
  `cause_of_issue` text NOT NULL,
  `gad_objective` text NOT NULL,
  `relevant_agency` varchar(255) NOT NULL,
  `generic_activity` text NOT NULL,
  `specific_activities` text NOT NULL,
  `male_participants` int NOT NULL,
  `female_participants` int NOT NULL,
  `total_participants` int NOT NULL,
  `gad_budget` decimal(15,2) NOT NULL,
  `source_of_budget` varchar(255) NOT NULL,
  `responsible_unit` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `campus` varchar(255) DEFAULT NULL,
  `year` int DEFAULT NULL,
  `total_gaa` decimal(15,2) DEFAULT NULL,
  `total_gad_fund` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gpb_entries`
--

INSERT INTO `gpb_entries` (`id`, `category`, `gender_issue`, `cause_of_issue`, `gad_objective`, `relevant_agency`, `generic_activity`, `specific_activities`, `male_participants`, `female_participants`, `total_participants`, `gad_budget`, `source_of_budget`, `responsible_unit`, `created_at`, `campus`, `year`, `total_gaa`, `total_gad_fund`) VALUES
(11, 'Client-Based', 'Test', '1', '1', 'Agency 1', '1', '1', 1, 1, 2, '1.00', 'Source 1', 'Unit 1', '2025-02-25 03:20:27', NULL, NULL, NULL, NULL),
(12, 'Gender Issue', 'e', 'e', 'e', 'Agency B', 'e; e', '[\"e\",\"e\"]', 2, 2, 4, '2.00', 'Source B', 'Unit A', '2025-03-10 02:02:07', 'Lipa', 2027, '800.00', '40.00'),
(13, 'Gender Issue', 'e', 'e', 'e', 'Agency B', 'e; e', '[\"e\",\"e\"]', 2, 2, 4, '2.00', 'Source B', 'Unit A', '2025-03-10 02:04:00', 'Lipa', 2027, '800.00', '40.00'),
(14, 'Gender Issue', 'e', 'e', 'e', 'Agency A', 'a; a', '[\"f\",\"f\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 02:04:30', 'Pablo Borbon', 2025, '1.00', '0.05'),
(15, 'Gender Issue', 'w', 'w', 'w', 'Agency B', 'w', '[\"w\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 02:06:03', 'Lipa', 2027, '800.00', '40.00'),
(16, 'Gender Issue', 'e', '2', 'e', 'Agency A', 'e', '[\"e\"]', 2, 2, 4, '2222.00', 'Source A', 'Unit B', '2025-03-10 02:08:38', 'Lipa', 2027, '800.00', '40.00'),
(17, 'Client-Focused', 'e', 'ee', 'e', 'Agency B', 'Default Activity', '[\"ee\",\"e\"]', 33, 3, 36, '553.00', 'Source B', 'Unit B', '2025-03-10 05:29:03', 'Lipa', 2027, '0.00', '0.00'),
(18, 'Client-Focused', 'j', 'j', 'n', 'Agency B', 'Default Activity', '[\"w\",\"w\"]', 2, 2, 4, '42.00', 'Source B', 'Unit B', '2025-03-10 05:30:40', 'Lipa', 2027, '0.00', '0.00'),
(19, 'Client-Focused', 'jj', 'j', 'd', 'Agency B', 'd', '[\"Default specific activity\"]', 2, 2, 4, '2222.00', 'Source B', 'Unit A', '2025-03-10 05:35:34', 'Lipa', 2027, '0.00', '0.00'),
(20, 'Client-Focused', 'ed', 'w', 'w', 'Agency A', 'General GAD Program', '[\"ww\",\"w\"]', 22, 22, 44, '2.00', 'Source B', 'Unit B', '2025-03-10 05:40:44', 'Lipa', 2027, '0.00', '0.00'),
(21, 'Organization-Focused', '2', '2', 'd', 'Agency B', 'ww', '[\"n\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 05:41:49', 'Default Campus', 2025, '0.00', '0.00'),
(22, 'Organization-Focused', 'n', 'f', 'f', 'Agency B', 'General GAD Program', '[\"f\",\"w\"]', 2, 22, 24, '2.00', 'Source B', 'Unit B', '2025-03-10 05:42:43', 'Pablo Borbon', 2025, '0.00', '0.00'),
(23, 'Client-Focused', 'Test Gender Issue', 'Test Cause', 'Test Objective', 'Agency A', 'Test Program', '[\"2\",\"d\"]', 10, 15, 25, '5000.00', 'Source A', 'Unit A', '2025-03-10 05:51:12', 'Lipa', 2027, '0.00', '0.00'),
(24, 'Client-Focused', 'nk', 'j', 'j', 'Agency B', 'b', '[\"j\",\"nb\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-10 06:06:33', 'Lipa', 2025, '0.00', '0.00'),
(25, 'Organization-Focused', 'dw', 'w', 'w', 'Agency B', 'w', '[\"2\"]', 4, 4, 8, '2222.00', 'Source B', 'Unit B', '2025-03-10 06:07:18', 'Lipa', 2026, '0.00', '0.00'),
(26, 'Client-Focused', 'nw', 'w', 'w', 'Agency B', 'www', '[\"eee\",\"eee\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 06:12:28', 'Lipa', 2026, '0.00', '0.00'),
(27, 'Client-Focused', 'nw', 'w', 'w', 'Agency B', 'wwwwww', '[\"eee\",\"eee\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-10 06:12:28', 'Lipa', 2026, '0.00', '0.00'),
(28, 'Organization-Focused', 'ww', 'dw', 'w', 'Agency B', '[\"www\",\"wwww\"]', '[\"ddwsa\",\"wdaw\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-10 06:18:30', 'Pablo Borbon', 2025, '0.00', '0.00'),
(29, 'Client-Focused', 'ejjj', 'e', '.f', 'Agency A', '[\"e\",\"e\"]', '[\"d\",\"d\"]', 2222, 22, 2244, '222.00', 'Source B', 'Unit B', '2025-03-10 06:58:20', 'Lipa', 2027, '0.00', '0.00'),
(30, 'Client-Focused', 'www', 'w', 'w', 'Agency A', '[\"www\",\"wwwwww\"]', '[\"wwwwww\",\"www\"]', 22, 22, 44, '2.00', 'Source A', 'Unit A', '2025-03-10 07:19:53', 'Lipa', 2025, '0.00', '0.00'),
(31, 'Organization-Focused', 'ek', 'c', 'w', 'Agency A', '[\"w\",\"w\"]', '[\"f\",\"wq\"]', 2, 0, 2, '222.00', 'Source A', 'Unit B', '2025-03-10 08:36:46', 'Pablo Borbon', 2025, '0.00', '0.00'),
(32, 'Organization-Focused', 'dwww', 'w', 'dddd', 'Agency A', '[\"w\",\"f\"]', '[\"f\",\",\"]', 7, 9, 16, '222.00', 'Source B', 'Unit B', '2025-03-10 08:37:59', 'Lipa', 2025, '0.00', '0.00'),
(33, 'Gender Issue', 'edw', 'e', 'e', 'Agency A', '[\"e\",\"f\"]', '[\"g\",\"g\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-11 01:34:00', 'Pablo Borbon', 2025, '1.00', '0.05'),
(34, 'Gender Issue', 'edw', 'e', 'e', 'Agency A', '[\"e\",\"f\"]', '[\"g\",\"g\"]', 2, 2, 4, '222.00', 'Source B', 'Unit B', '2025-03-11 01:34:00', 'Pablo Borbon', 2025, '1.00', '0.05'),
(35, 'Client-Focused', 'df', 'fe', 'f', 'Agency B', '[\"ww\",\"e\"]', '[\"w\",\"wwww\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-11 01:37:25', 'Lipa', 2026, '1000.00', '50.00'),
(36, 'Client-Focused', 'df', 'fe', 'f', 'Agency B', '[\"ww\",\"e\"]', '[\"w\",\"wwww\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-11 01:37:25', 'Lipa', 2026, '1000.00', '50.00'),
(37, 'Client-Focused', 'fw', 'd', 'w', 'Agency B', '[\"w\",\"f\"]', '[\"d\",\"d\"]', 2, 2, 4, '3.00', 'Source B', 'Unit B', '2025-03-11 01:40:55', 'Pablo Borbon', 2025, '1.00', '0.05'),
(38, 'Client-Focused', 'fw', 'd', 'w', 'Agency B', '[\"w\",\"f\"]', '[\"d\",\"d\"]', 2, 2, 4, '3.00', 'Source B', 'Unit B', '2025-03-11 01:40:55', 'Pablo Borbon', 2025, '1.00', '0.05'),
(39, 'Client-Focused', 'fw', 'd', 'w', 'Agency B', '[\"w\",\"f\"]', '[\"d\",\"d\"]', 2, 2, 4, '3.00', 'Source B', 'Unit B', '2025-03-11 01:40:55', 'Pablo Borbon', 2025, '1.00', '0.05'),
(40, 'Client-Focused', 'fw', 'd', 'w', 'Agency B', '[\"w\",\"f\"]', '[\"d\",\"d\"]', 2, 2, 4, '3.00', 'Source B', 'Unit B', '2025-03-11 01:40:55', 'Pablo Borbon', 2025, '1.00', '0.05'),
(41, 'Organization-Focused', 'dwwww', 'd', 'g', 'Agency B', '[\"d\",\"d\"]', '[\"cw\",\"wq\"]', 2, 2, 4, '22222.00', 'Source B', 'Unit B', '2025-03-11 01:46:47', 'Pablo Borbon', 2025, '1.00', '0.05'),
(42, 'Organization-Focused', 'dwwww', 'd', 'g', 'Agency B', '[\"d\",\"d\"]', '[\"cw\",\"wq\"]', 2, 2, 4, '22222.00', 'Source B', 'Unit B', '2025-03-11 01:46:47', 'Pablo Borbon', 2025, '1.00', '0.05'),
(43, 'Organization-Focused', 'eeee', 'e', 'e', 'Agency B', '[\"e\",\"f\"]', '[\"e\",\"e\"]', 3, 3, 6, '3.00', 'Source B', 'Unit B', '2025-03-12 00:21:19', 'Pablo Borbon', 2025, '1.00', '0.05'),
(44, 'Organization-Focused', 'eeee', 'e', 'e', 'Agency B', '[\"e\",\"f\"]', '[\"e\",\"e\"]', 3, 3, 6, '3.00', 'Source B', 'Unit B', '2025-03-12 00:21:19', 'Pablo Borbon', 2025, '1.00', '0.05'),
(45, 'Client-Focused', 'e', 'e', 'e', 'Agency A', '[\"e\",\"e\"]', '[\"e\",\"e\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-17 04:54:10', 'Lipa', 2027, '800.00', '40.00'),
(46, 'Client-Focused', 'e', 'e', 'e', 'Agency A', '[\"e\",\"e\"]', '[\"e\",\"e\"]', 2, 2, 4, '2.00', 'Source B', 'Unit B', '2025-03-17 04:54:10', 'Lipa', 2027, '800.00', '40.00');

-- --------------------------------------------------------

--
-- Table structure for table `personnel`
--

DROP TABLE IF EXISTS `personnel`;
CREATE TABLE IF NOT EXISTS `personnel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `academic_rank` varchar(100) NOT NULL,
  `campus` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_academic_rank` (`academic_rank`)
) ENGINE=MyISAM AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`id`, `name`, `category`, `status`, `gender`, `academic_rank`, `campus`, `created_at`) VALUES
(117, 'Elbert D. Nebres', 'Teaching', 'Guest Lecturer', 'male', 'Instructor II', 'Lipa', '2025-03-05 05:16:51'),
(116, 'Elbert D. Nebres', 'Teaching', 'Permanent', 'male', 'Instructor II', 'Alangilan', '2025-03-05 05:00:08'),
(132, 'Test', 'Teaching', 'Temporary', 'female', 'Instructor III', 'Lipa', '2025-03-06 01:10:13'),
(124, 'Fryan Auric L. Valdez', 'Teaching', 'Guest Lecturer', 'Gay', 'Instructor I', 'Lipa', '2025-03-05 05:43:54');

-- --------------------------------------------------------

--
-- Table structure for table `personnel_list`
--

DROP TABLE IF EXISTS `personnel_list`;
CREATE TABLE IF NOT EXISTS `personnel_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `gender` enum('male','female','gay','lesbian') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `academic_rank_id` int DEFAULT NULL,
  `monthly_salary` decimal(10,2) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_rank_id` (`academic_rank_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `personnel_list`
--

INSERT INTO `personnel_list` (`id`, `name`, `gender`, `academic_rank_id`, `monthly_salary`, `hourly_rate`) VALUES
(1, 'John Doe', 'male', 1, '50000.00', '312.50'),
(2, 'Jane Smith', 'female', 2, '45000.00', '281.25'),
(3, 'John Doe', 'male', 1, '50000.00', '250.00'),
(4, 'Jane Smith', 'female', 2, '55000.00', '275.00'),
(5, 'Michael Johnson', 'gay', 3, '60000.00', '300.00'),
(6, 'Emily Davis', 'lesbian', 1, '52000.00', '260.00'),
(7, 'Daniel Brown', 'gay', 2, '58000.00', '290.00'),
(8, 'Sophia Wilson', 'lesbian', 3, '62000.00', '310.00'),
(9, 'Matthew Martinez', 'male', 1, '51000.00', '255.00'),
(10, 'Olivia Anderson', 'female', 2, '57000.00', '285.00'),
(11, 'Ethan Thomas', 'male', 3, '63000.00', '315.00'),
(12, 'Ava Taylor', 'female', 1, '53000.00', '265.00');

-- --------------------------------------------------------

--
-- Table structure for table `ppas_beneficiaries`
--

DROP TABLE IF EXISTS `ppas_beneficiaries`;
CREATE TABLE IF NOT EXISTS `ppas_beneficiaries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_id` int NOT NULL,
  `type` enum('internal_student','internal_faculty','external') NOT NULL,
  `male_count` int DEFAULT '0',
  `female_count` int DEFAULT '0',
  `external_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ppas_id` (`ppas_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_beneficiaries`
--

INSERT INTO `ppas_beneficiaries` (`id`, `ppas_id`, `type`, `male_count`, `female_count`, `external_type`) VALUES
(1, 1, 'internal_student', 23, 3, NULL),
(2, 1, 'internal_faculty', 3, 2, NULL),
(3, 1, 'external', 2, 2, '2'),
(4, 2, 'internal_student', 9, 8, NULL),
(5, 2, 'internal_faculty', 3, 2, NULL),
(6, 2, 'external', 2, 2, 'j'),
(7, 3, 'internal_student', 12, 22, NULL),
(8, 3, 'internal_faculty', 1, 2, NULL),
(9, 3, 'external', 2, 2, '2'),
(10, 4, 'internal_student', 2, 2, NULL),
(11, 4, 'internal_faculty', 0, 3, NULL),
(12, 4, 'external', 2, 2, 'fw'),
(13, 5, 'internal_student', 2, 2, NULL),
(14, 5, 'internal_faculty', 1, 2, NULL),
(15, 5, 'external', 2, 2, '2'),
(16, 6, 'internal_student', 2, 2, NULL),
(17, 6, 'internal_faculty', 1, 3, NULL),
(18, 6, 'external', 2, 2, '2');

-- --------------------------------------------------------

--
-- Table structure for table `ppas_forms`
--

DROP TABLE IF EXISTS `ppas_forms`;
CREATE TABLE IF NOT EXISTS `ppas_forms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `quarter` varchar(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `has_lunch_break` tinyint(1) DEFAULT '0',
  `has_am_break` tinyint(1) DEFAULT '0',
  `has_pm_break` tinyint(1) DEFAULT '0',
  `total_duration` decimal(10,2) NOT NULL,
  `approved_budget` decimal(10,2) NOT NULL,
  `source_of_budget` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'draft',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_forms`
--

INSERT INTO `ppas_forms` (`id`, `year`, `quarter`, `title`, `location`, `date`, `start_time`, `end_time`, `has_lunch_break`, `has_am_break`, `has_pm_break`, `total_duration`, `approved_budget`, `source_of_budget`, `created_at`, `updated_at`, `created_by`, `status`) VALUES
(1, 2021, 'Q2', 'dwww', 'h', '2025-03-19', '05:24:00', '13:24:00', 1, 0, 0, '8.00', '2.00', 'Income', '2025-03-11 06:28:45', '2025-03-11 06:28:45', 'Lipa', 'draft'),
(2, 2023, 'Q1', 'n', 'n', '2025-03-13', '03:12:00', '15:13:00', 0, 0, 0, '12.02', '22222.00', 'Grants', '2025-03-11 07:16:14', '2025-03-11 07:16:14', 'Lipa', 'draft'),
(3, 2021, 'Q2', 'w', 'w', '2025-02-27', '09:35:00', '21:35:00', 1, 0, 0, '12.00', '2.00', 'Income', '2025-03-17 01:37:07', '2025-03-17 01:37:07', 'Lipa', 'draft'),
(4, 2021, 'Q1', 'dwwww', 'w', '2025-03-17', '10:21:00', '22:21:00', 1, 0, 0, '12.00', '3.00', 'GAA', '2025-03-17 02:23:27', '2025-03-17 02:23:27', 'Lipa', 'draft'),
(5, 2021, 'Q2', 'g', 'g', '2025-03-21', '10:47:00', '22:47:00', 0, 0, 0, '12.00', '2222222.00', 'Income', '2025-03-17 02:47:54', '2025-03-17 02:47:54', 'Lipa', 'draft'),
(6, 2021, 'Q1', 'ed', 'e', '2025-03-27', '10:55:00', '22:55:00', 1, 0, 0, '12.00', '3.00', 'GAA', '2025-03-17 02:57:06', '2025-03-17 02:57:06', 'Lipa', 'draft');

-- --------------------------------------------------------

--
-- Table structure for table `ppas_personnel`
--

DROP TABLE IF EXISTS `ppas_personnel`;
CREATE TABLE IF NOT EXISTS `ppas_personnel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_id` int NOT NULL,
  `personnel_id` int NOT NULL,
  `personnel_name` varchar(255) NOT NULL,
  `role` enum('project_leader','asst_project_leader','project_staff','other_participant') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ppas_id` (`ppas_id`),
  KEY `personnel_id` (`personnel_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_personnel`
--

INSERT INTO `ppas_personnel` (`id`, `ppas_id`, `personnel_id`, `personnel_name`, `role`) VALUES
(1, 1, 2, '', 'project_leader'),
(2, 1, 1, '', 'asst_project_leader'),
(3, 1, 1, '', 'project_staff'),
(4, 1, 2, '', 'other_participant'),
(5, 1, 1, '', 'other_participant'),
(6, 2, 2, 'Jane Smith', 'project_leader'),
(7, 2, 1, 'John Doe', 'project_leader'),
(8, 2, 5, 'Michael Johnson', 'asst_project_leader'),
(9, 2, 8, 'Sophia Wilson', 'project_staff'),
(10, 2, 7, 'Daniel Brown', 'other_participant'),
(11, 3, 6, 'Emily Davis', 'project_leader'),
(12, 3, 7, 'Daniel Brown', 'asst_project_leader'),
(13, 3, 2, 'Jane Smith', 'project_staff'),
(14, 3, 1, 'John Doe', 'project_staff'),
(15, 3, 12, 'Ava Taylor', 'other_participant'),
(16, 3, 6, 'Emily Davis', 'other_participant');

-- --------------------------------------------------------

--
-- Table structure for table `ppas_sdgs`
--

DROP TABLE IF EXISTS `ppas_sdgs`;
CREATE TABLE IF NOT EXISTS `ppas_sdgs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ppas_id` int NOT NULL,
  `sdg_number` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ppas_id` (`ppas_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ppas_sdgs`
--

INSERT INTO `ppas_sdgs` (`id`, `ppas_id`, `sdg_number`) VALUES
(1, 1, 4),
(2, 2, 4),
(3, 3, 2),
(4, 4, 2),
(5, 5, 3),
(6, 6, 2);

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
CREATE TABLE IF NOT EXISTS `programs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_name` (`program_name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `program_name`, `created_at`) VALUES
(1, 'eff', '2025-03-17 01:06:21'),
(2, 'fwa', '2025-03-17 01:09:58'),
(3, 'g', '2025-03-17 01:11:35'),
(4, 'wwww', '2025-03-17 01:14:00'),
(5, 'w', '2025-03-17 01:19:12'),
(6, 'f', '2025-03-17 01:21:45'),
(7, 'wff', '2025-03-17 02:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_name` (`project_name`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_name`, `created_at`) VALUES
(1, 'e', '2025-03-17 01:06:35'),
(2, 'fawa', '2025-03-17 01:06:43'),
(3, 'fese', '2025-03-17 01:11:48'),
(4, 'fwa', '2025-03-17 01:17:04'),
(5, 'www', '2025-03-17 01:19:23'),
(6, 'd', '2025-03-17 02:00:12');

-- --------------------------------------------------------

--
-- Table structure for table `target`
--

DROP TABLE IF EXISTS `target`;
CREATE TABLE IF NOT EXISTS `target` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year` year NOT NULL,
  `campus` enum('Lipa','Pablo Borbon','Alangilan','Nasugbu','Malvar''Rosario','Balayan','Lemery','San Juan','Lobo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_gaa` decimal(15,2) NOT NULL,
  `total_gad_fund` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_year_campus` (`year`,`campus`),
  KEY `idx_year` (`year`),
  KEY `idx_campus` (`campus`)
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `target`
--

INSERT INTO `target` (`id`, `year`, `campus`, `total_gaa`, `total_gad_fund`) VALUES
(120, 2025, 'Pablo Borbon', '1.00', '0.05'),
(146, 2025, 'Alangilan', '500.00', '25.00'),
(149, 2026, 'Alangilan', '1.00', '0.05'),
(152, 2025, 'Lipa', '420.00', '21.00'),
(154, 2026, 'Lipa', '1000.00', '50.00'),
(155, 2027, 'Lipa', '800.00', '40.00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
