-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 10, 2025 at 06:31 AM
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
-- Table structure for table `signatories`
--

DROP TABLE IF EXISTS `signatories`;
CREATE TABLE IF NOT EXISTS `signatories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name1` varchar(255) NOT NULL,
  `gad_head_secretariat` varchar(255) NOT NULL,
  `name2` varchar(255) NOT NULL,
  `vice_chancellor_rde` varchar(255) NOT NULL,
  `name3` varchar(255) NOT NULL,
  `chancellor` varchar(255) NOT NULL,
  `name4` varchar(255) NOT NULL,
  `asst_director_gad` varchar(255) NOT NULL,
  `name5` varchar(255) NOT NULL,
  `head_extension_services` varchar(255) NOT NULL,
  `campus` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `signatories`
--

INSERT INTO `signatories` (`id`, `name1`, `gad_head_secretariat`, `name2`, `vice_chancellor_rde`, `name3`, `chancellor`, `name4`, `asst_director_gad`, `name5`, `head_extension_services`, `campus`, `created_at`, `updated_at`) VALUES
(3, 'Micah Reynolds\n\n', 'GAD Head Secretariat', 'Julian Hayes', 'Vice Chancellor For Research, Development and Extension', 'Aria Sullivan\n\n', 'Chancellor', 'Ezra Mitchell\n\n', 'Assistant Director For GAD Advocacies', 'Lila Gardner\n\n', 'Head of Extension Services', 'Malvar', '2025-04-03 08:25:31', '2025-04-07 00:27:02'),
(4, 'Liam Carters', 'GAD Head Secretariat', 'Ava Thompson', 'Vice Chancellor For Research, Development and Extension', 'Noah Reyes', 'Chancellor', 'Maya Collins', 'Assistant Director For GAD Advocacies', 'Elijah Brooks', 'Head of Extension Services', 'Lipa', '2025-04-03 08:35:45', '2025-04-07 01:49:10'),
(5, 'Sofia Bennett', 'GAD Head Secretariat', 'Caleb Navarro', 'Vice Chancellor For Research, Development and Extension', 'Chloe Ramsey', 'Chancellor', 'Lucas Avery', 'Assistant Director For GAD Advocacies', 'Isla Monroe', 'Head of Extension Services', 'Alangilan', '2025-04-07 00:10:02', '2025-04-07 00:10:02'),
(6, 'Elena Harper', 'GAD Head Secretariat', 'Elena Harper', 'Vice Chancellor For Research, Development and Extension', 'Nina Caldwell', 'Chancellor', 'Owen Blake', 'Assistant Director For GAD Advocacies', 'Zoe Chambers', 'Head of Extension Services', 'Rosario', '2025-04-07 03:01:41', '2025-04-07 03:01:41');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
