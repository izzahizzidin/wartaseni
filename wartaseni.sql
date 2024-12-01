-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2024 at 06:09 PM
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
-- Database: `wartaseni`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin01', 'adminsatu');

-- --------------------------------------------------------

--
-- Table structure for table `admin_replies`
--

CREATE TABLE `admin_replies` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `reply` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_replies`
--

INSERT INTO `admin_replies` (`id`, `form_id`, `admin_id`, `reply`, `created_at`) VALUES
(1, 1, 1, 'test reply will it work again', '2024-06-23 23:48:36'),
(2, 2, 1, 'oh nice it works!', '2024-06-24 01:49:02');

-- --------------------------------------------------------

--
-- Table structure for table `artworks`
--

CREATE TABLE `artworks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `media_type` enum('image','video') NOT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artworks`
--

INSERT INTO `artworks` (`id`, `user_id`, `file_path`, `title`, `description`, `created_at`, `updated_at`, `media_type`, `category`) VALUES
(5, 2, 'uploads/6675b0b55976a.jpg', 'Railing Pose', 'a sitting pose study from bottom angle', '2024-06-22 00:56:21', '2024-06-22 16:05:21', 'image', 'Digital Painting'),
(6, 2, 'uploads/6675b10e1ac8f.jpg', 'City Girl', 'girl vibing in the city, a background study', '2024-06-22 00:57:50', '2024-06-22 16:02:41', 'image', 'Digital Painting'),
(7, 2, 'uploads/6675b21d9bb78.jpg', 'かばん', 'a travel bag study', '2024-06-22 01:02:21', '2024-06-22 16:05:46', 'image', 'Digital Painting'),
(8, 2, 'uploads/6676862f72283.jpg', 'Country City', 'a background study of a far away city at night', '2024-06-22 16:07:11', '2024-06-22 16:07:11', 'image', 'Digital Painting'),
(9, 2, 'uploads/66768663a8f74.jpg', 'One Day', 'a landscape study of a sunset with jagged mountain view', '2024-06-22 16:08:03', '2024-06-22 16:08:03', 'image', 'Digital Painting'),
(10, 2, 'uploads/667686a18385e.jpg', 'Landscape', ' a landscape study in a fantasy world', '2024-06-22 16:09:05', '2024-06-22 16:09:05', 'image', 'Digital Painting'),
(11, 2, 'uploads/667686d2612a1.jpg', 'Crazies', 'a texture study in the forest', '2024-06-22 16:09:54', '2024-06-22 16:09:54', 'image', 'Digital Painting'),
(12, 2, 'uploads/667686f608bb7.jpg', 'Sitting Study', 'a girl squatting pose study', '2024-06-22 16:10:30', '2024-06-22 16:10:30', 'image', 'Digital Painting'),
(13, 2, 'uploads/667687175c9ae.mp4', 'Railing Pose speed paint', 'a speed paint of my art study', '2024-06-22 16:11:03', '2024-06-22 16:11:03', 'video', 'Speedpaint'),
(14, 2, 'uploads/6676876985cbb.jpg', 'OC Challenge', 'an OC challenge based on myself', '2024-06-22 16:12:25', '2024-06-22 16:12:25', 'image', 'Portraits'),
(15, 3, 'uploads/667692c61ecb3.jpg', 'malay fantasy background', 'background at fantasy world based on malay culture', '2024-06-22 17:00:54', '2024-06-24 02:09:39', 'image', 'Backgrounds'),
(16, 3, 'uploads/6676a5199b057.jpg', 'young man', 'young male character', '2024-06-22 18:19:05', '2024-06-22 18:19:05', 'image', 'Portraits'),
(17, 3, 'uploads/6676a5900d2b1.jpg', 'Watarai Hibari', 'The heir of a family of thieves.\r\nHe aspires to be a righteous \"Great Thief\" who captures people\'s hearts.\r\nSince he does not steal for money, he earns his living by working part-time in a cafe.', '2024-06-22 18:21:04', '2024-06-22 18:21:04', 'image', 'Portraits'),
(18, 3, 'uploads/6676a6a98b52b.jpg', 'the princess and her knight', 'cute and romantic view of the local princess and knight', '2024-06-22 18:25:45', '2024-06-22 18:25:45', 'image', 'Digital Painting'),
(19, 3, 'uploads/6676a77c52ab1.jpg', 'the princess', 'the female lead of my upcoming graphic novel series', '2024-06-22 18:29:16', '2024-06-22 18:29:16', 'image', 'Portraits'),
(20, 3, 'uploads/6676a982af426.jpg', 'background study 2', 'made another background study during my free time', '2024-06-22 18:37:54', '2024-06-24 02:12:35', 'image', 'Backgrounds'),
(21, 3, 'uploads/6676a9cfa2e08.jpg', 'sample panel from my graphic novel', 'sample panel from my graphic novel. updates every week', '2024-06-22 18:39:11', '2024-06-22 18:39:11', 'image', 'Portraits'),
(22, 3, 'uploads/6676aa699abf6.jpg', 'cloudy skies', 'cloudy skies with a mythical building in the background', '2024-06-22 18:41:45', '2024-06-22 18:41:45', 'image', 'Digital Painting'),
(23, 3, 'uploads/6676aaca3deb7.jpg', 'national day art', 'selamat hari merdeka!', '2024-06-22 18:43:22', '2024-06-22 18:43:22', 'image', 'Portraits'),
(24, 3, 'uploads/6676ab3825140.mp4', 'malay fantasy background speedpaint', 'speedpaint of malay fantasy background', '2024-06-22 18:45:12', '2024-06-22 18:45:12', 'video', 'Speedpaint'),
(25, 4, 'uploads/6677edf0d3f2b.jpg', 'Tsukishima Kei and Yamaguchi Tadashi as Edogawa Ranpo and Edgar Allan Poe', 'Tsukishima Kei and Yamaguchi Tadashi as Edogawa Ranpo and Edgar Allan Poe\r\ncrossover 1 between Haikyuu!! and Bungou Stray Dogs', '2024-06-23 17:42:08', '2024-06-23 17:42:08', 'image', 'Digital Painting'),
(26, 4, 'uploads/6677ee3199a18.jpg', 'Edogawa Ranpo and Edgar Allan Poe as Tsukishima Kei and Yamaguchi Tadashi', 'Edogawa Ranpo and Edgar Allan Poe as Tsukishima Kei and Yamaguchi Tadashi\r\ncrossover 2 between Haikyuu!! and Bungou Stray Dogs', '2024-06-23 17:43:13', '2024-06-23 17:43:13', 'image', 'Digital Painting'),
(27, 4, 'uploads/6677efbb1a560.jpg', 'Young Man Portrait', 'a portrait of a young man', '2024-06-23 17:49:47', '2024-06-23 17:49:47', 'image', 'Photo Painting'),
(28, 4, 'uploads/6677f12c05c7c.jpg', 'Akutagawa Ryūnosuke', 'Akutagawa Ryūnosuke from Bungou Stray Dogs', '2024-06-23 17:55:56', '2024-06-23 17:55:56', 'image', 'Digital Painting'),
(29, 4, 'uploads/6677f1d850558.jpg', 'Uchiyama Kouki', 'Uchiyama Kouki is a Japanese actor who specializes in voice acting. He is affiliated with Himawari Theatre Group. He won Best Male Rookie at 5th Seiyu Awards. He also received one of Best Voice Actors at Tokyo Anime Award Festival in 2015.', '2024-06-23 17:58:48', '2024-06-23 17:58:48', 'image', 'Portraits'),
(30, 4, 'uploads/6677f24209814.jpg', 'Akutagawa Ryūnosuke Closeup', 'Akutagawa Ryūnosuke art 2', '2024-06-23 18:00:34', '2024-06-23 18:00:34', 'image', 'Portraits'),
(31, 4, 'uploads/6677f360316b0.jpg', 'Kim Hye-sung', 'Kim Hye-sung was born on September 23, at 20 years of age, Hye-sung had a height of 164cm, weight: 50kg.', '2024-06-23 18:05:20', '2024-06-23 18:05:20', 'image', 'Digital Painting'),
(32, 4, 'uploads/6677f466099cf.jpg', 'Kunigami Rensuke and Chigiri Hyōma', 'Kunigami Rensuke and Chigiri Hyōma from the hit series Blue Lock', '2024-06-23 18:09:42', '2024-06-23 18:09:42', 'image', 'Digital Painting'),
(33, 4, 'uploads/6677f4993e5f9.jpg', 'young man in silly hat', 'young man art with a silly hat', '2024-06-23 18:10:33', '2024-06-23 18:10:33', 'image', 'Drawings'),
(34, 4, 'uploads/6677f6722a030.jpg', 'Nagi Seishirō', 'Nagi Seishirō from the hit series Blue Lock', '2024-06-23 18:18:26', '2024-06-23 18:18:26', 'image', 'Graphic Novels'),
(35, 4, 'uploads/6677faa57a6d4.jpg', '2 guys watercolour painting', '2 guys watercolour painting', '2024-06-23 18:36:21', '2024-06-23 18:36:21', 'image', 'Digital Painting'),
(36, 5, 'uploads/667808536cfd7.png', '03 12 22', 'created on 03 12 22', '2024-06-23 19:34:43', '2024-06-23 19:34:43', 'image', 'Drawings'),
(37, 5, 'uploads/66780964a5cbf.png', '07 07 23', '07 07 23\r\n\r\nninomae ina\'nis', '2024-06-23 19:39:16', '2024-06-23 19:39:16', 'image', 'Drawings'),
(38, 5, 'uploads/667809cdb135e.png', '14 08 23 comm zhen', '14 08 23\r\ncommission', '2024-06-23 19:41:01', '2024-06-23 19:41:01', 'image', 'Commissioned Artworks'),
(39, 5, 'uploads/66780aba19e41.png', '14 11 23', '14 11 23\r\nschool rebel', '2024-06-23 19:44:58', '2024-06-23 19:44:58', 'image', 'Drawings'),
(40, 5, 'uploads/66780b0fe48b5.png', '20 07 22 hatsune miku', '20 07 22 hatsune miku', '2024-06-23 19:46:23', '2024-06-23 19:46:23', 'image', 'Drawings'),
(41, 5, 'uploads/66780bc56ec27.png', '21 09 23 gura resize', '21 09 23 gura resize\r\n22 09 23\r\nsorry for lack of upload, I took 1 week off (supposedly) but ended up extending it lol\r\n\r\nso here\'s gura for you', '2024-06-23 19:49:25', '2024-06-23 19:49:25', 'image', 'Drawings'),
(43, 5, 'uploads/66780e5361e69.png', '07 02 24', '07 02 24\r\nI’m back!!', '2024-06-23 20:00:19', '2024-06-23 20:00:19', 'image', 'Drawings'),
(44, 5, 'uploads/6678108059891.png', '26 08 23 oc', '26 08 23 oc full-coloured', '2024-06-23 20:09:36', '2024-06-23 20:09:36', 'image', 'Portraits');

-- --------------------------------------------------------

--
-- Table structure for table `artwork_tags`
--

CREATE TABLE `artwork_tags` (
  `artwork_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artwork_tags`
--

INSERT INTO `artwork_tags` (`artwork_id`, `tag_id`) VALUES
(5, 8),
(5, 32),
(5, 51),
(5, 53),
(5, 54),
(5, 55),
(5, 88),
(5, 89),
(6, 6),
(6, 8),
(6, 17),
(6, 18),
(6, 19),
(6, 25),
(6, 55),
(6, 90),
(6, 91),
(7, 21),
(7, 35),
(7, 42),
(7, 43),
(7, 55),
(7, 92),
(8, 23),
(8, 24),
(8, 25),
(8, 26),
(8, 27),
(8, 28),
(8, 29),
(8, 30),
(9, 6),
(9, 29),
(9, 31),
(9, 32),
(9, 33),
(9, 34),
(9, 35),
(9, 36),
(10, 33),
(10, 34),
(10, 36),
(10, 37),
(10, 38),
(10, 39),
(10, 40),
(10, 41),
(10, 42),
(10, 43),
(11, 38),
(11, 42),
(11, 44),
(11, 45),
(11, 46),
(11, 47),
(11, 48),
(12, 49),
(12, 50),
(12, 51),
(12, 52),
(12, 53),
(12, 54),
(13, 8),
(13, 54),
(13, 55),
(13, 56),
(13, 57),
(13, 58),
(14, 26),
(14, 59),
(14, 60),
(14, 61),
(14, 62),
(14, 63),
(15, 17),
(15, 38),
(15, 43),
(16, 11),
(16, 15),
(16, 26),
(16, 43),
(16, 64),
(16, 65),
(17, 5),
(17, 6),
(17, 26),
(17, 59),
(17, 66),
(17, 67),
(17, 68),
(17, 69),
(17, 70),
(18, 4),
(18, 8),
(18, 26),
(18, 43),
(18, 60),
(18, 71),
(18, 72),
(19, 3),
(19, 6),
(19, 8),
(19, 43),
(19, 73),
(19, 74),
(19, 75),
(20, 17),
(20, 32),
(20, 38),
(20, 76),
(20, 77),
(21, 26),
(21, 43),
(21, 69),
(21, 78),
(21, 79),
(21, 80),
(21, 81),
(22, 17),
(22, 32),
(22, 43),
(22, 76),
(22, 82),
(22, 83),
(23, 9),
(23, 26),
(23, 64),
(23, 65),
(23, 69),
(23, 76),
(23, 84),
(23, 85),
(23, 86),
(24, 17),
(24, 38),
(24, 43),
(24, 87),
(25, 5),
(25, 93),
(25, 94),
(25, 95),
(25, 96),
(25, 97),
(25, 98),
(25, 99),
(26, 5),
(26, 93),
(26, 94),
(26, 95),
(26, 96),
(26, 97),
(26, 98),
(26, 99),
(27, 65),
(27, 80),
(27, 81),
(27, 100),
(27, 101),
(28, 5),
(28, 98),
(28, 102),
(29, 5),
(29, 101),
(29, 103),
(29, 104),
(30, 5),
(30, 98),
(30, 102),
(30, 105),
(31, 5),
(31, 64),
(31, 76),
(31, 100),
(31, 106),
(32, 5),
(32, 35),
(32, 69),
(32, 107),
(32, 108),
(32, 109),
(33, 15),
(33, 38),
(33, 69),
(33, 100),
(33, 110),
(34, 5),
(34, 38),
(34, 69),
(34, 100),
(34, 109),
(34, 111),
(34, 112),
(35, 3),
(35, 69),
(35, 76),
(35, 113),
(35, 114),
(36, 4),
(36, 8),
(36, 15),
(36, 26),
(36, 72),
(36, 80),
(36, 115),
(37, 5),
(37, 8),
(37, 67),
(37, 70),
(37, 79),
(37, 80),
(37, 81),
(37, 116),
(37, 117),
(38, 5),
(38, 8),
(38, 60),
(38, 80),
(38, 81),
(38, 118),
(38, 119),
(38, 120),
(38, 121),
(39, 8),
(39, 79),
(39, 80),
(39, 81),
(39, 122),
(39, 123),
(39, 124),
(40, 5),
(40, 8),
(40, 79),
(40, 80),
(40, 81),
(40, 125),
(40, 126),
(40, 127),
(40, 128),
(40, 129),
(41, 5),
(41, 8),
(41, 67),
(41, 70),
(41, 79),
(41, 80),
(41, 81),
(41, 117),
(41, 130),
(43, 8),
(43, 15),
(43, 65),
(43, 79),
(43, 80),
(43, 81),
(43, 131),
(44, 6),
(44, 15),
(44, 38),
(44, 59),
(44, 132);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `artwork_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `artwork_id`, `comment`, `created_at`) VALUES
(1, 2, 6, 'I like the colours!', '2024-06-22 23:41:01'),
(5, 1, 40, 'yeay miku!!', '2024-06-23 23:09:47'),
(6, 1, 6, 'pretty purple and stars', '2024-06-23 23:10:36'),
(8, 5, 40, 'mikuuu', '2024-07-17 00:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `contact_forms`
--

CREATE TABLE `contact_forms` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('open','closed') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_forms`
--

INSERT INTO `contact_forms` (`id`, `user_id`, `name`, `email`, `message`, `created_at`, `status`) VALUES
(1, 1, 'Nim Mi', 'tenyukinim@gmail.com', 'test message again because I am very lost', '2024-06-23 23:45:31', 'closed'),
(2, 4, 'N Farhana', 'farhana@mail.com', 'test message 2. please help', '2024-06-24 01:48:38', 'closed');

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`follower_id`, `following_id`) VALUES
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(2, 1),
(4, 1),
(4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `artwork_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `artwork_id`, `created_at`) VALUES
(4, 2, 19, '2024-06-22 23:45:02'),
(9, 1, 40, '2024-06-23 23:09:37'),
(10, 1, 6, '2024-06-23 23:10:16'),
(12, 4, 14, '2024-06-24 00:13:51'),
(13, 4, 6, '2024-06-24 00:14:19'),
(16, 5, 5, '2024-07-16 23:59:32'),
(17, 5, 10, '2024-07-16 23:59:43'),
(18, 5, 12, '2024-07-16 23:59:58'),
(19, 5, 23, '2024-07-17 00:00:11'),
(20, 5, 19, '2024-07-17 00:00:21'),
(21, 5, 15, '2024-07-17 00:00:27'),
(22, 5, 31, '2024-07-17 00:01:12'),
(23, 5, 29, '2024-07-17 00:01:20'),
(24, 5, 27, '2024-07-17 00:01:27'),
(25, 5, 24, '2024-07-17 00:01:32'),
(26, 5, 35, '2024-07-17 00:01:44'),
(27, 4, 44, '2024-07-17 00:02:27'),
(28, 4, 41, '2024-07-17 00:02:41'),
(29, 4, 39, '2024-07-17 00:02:54'),
(30, 4, 37, '2024-07-17 00:03:01'),
(31, 4, 24, '2024-07-17 00:03:37'),
(32, 4, 21, '2024-07-17 00:03:51'),
(33, 4, 19, '2024-07-17 00:04:04'),
(34, 4, 17, '2024-07-17 00:04:17'),
(35, 4, 9, '2024-07-17 00:04:32'),
(36, 4, 13, '2024-07-17 00:04:39'),
(37, 4, 5, '2024-07-17 00:04:44'),
(38, 2, 39, '2024-07-17 00:05:20'),
(39, 2, 36, '2024-07-17 00:05:33'),
(40, 2, 44, '2024-07-17 00:05:40');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `category`, `message`, `created_at`) VALUES
(1, 2, 1, 'Personal Art Commission', 'Hi, I would like to commission your art :D', '2024-06-25 01:41:02'),
(2, 1, 2, 'Reply', 'Yes, I accept your request. May I know your request?', '2024-06-25 02:41:33'),
(3, 2, 1, 'Reply', 'thank you so much! I need a portrait to be done in 2 weeks. can be monochrome palette', '2024-06-25 02:54:01'),
(4, 4, 2, 'Business Purposes', 'let\'s do a collaboration drawing soon!', '2024-07-03 08:34:48'),
(5, 4, 2, 'Reply', 'I can do the background', '2024-07-03 08:35:09'),
(6, 4, 2, 'Reply', 'hello', '2024-07-03 12:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'You have a new message from oolington.', 0, '2024-06-25 01:41:02'),
(2, 2, 'You have a new reply from nimmi87.', 0, '2024-06-25 02:41:33'),
(3, 1, 'You have a new reply from oolington.', 0, '2024-06-25 02:54:01'),
(4, 2, 'You have a new message from keinodino.', 0, '2024-07-03 08:34:48'),
(5, 2, 'You have a new reply from keinodino.', 0, '2024-07-03 08:35:09'),
(6, 2, 'You have a new reply from keinodino.', 0, '2024-07-03 12:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `tag_name`) VALUES
(113, '2 guys'),
(42, 'adventure'),
(102, 'Akutagawa Ryūnosuke'),
(50, 'anatomy'),
(63, 'anatomy study'),
(55, 'art study'),
(17, 'background'),
(21, 'bag'),
(1, 'bear'),
(82, 'birds'),
(80, 'black'),
(76, 'blue'),
(109, 'blue lock'),
(26, 'boy'),
(3, 'brown'),
(91, 'buildings'),
(98, 'bungou stray dogs'),
(45, 'bushes'),
(89, 'calm'),
(41, 'castle'),
(27, 'cat'),
(61, 'challenge'),
(11, 'character'),
(47, 'cheerful'),
(108, 'Chigiri Hyōma'),
(25, 'city'),
(16, 'city study'),
(105, 'closeup'),
(52, 'clothes'),
(88, 'clothes study'),
(30, 'cold'),
(56, 'coloring'),
(22, 'coloured'),
(139, 'colours'),
(118, 'commission'),
(24, 'country side'),
(29, 'cozy'),
(99, 'crossover'),
(4, 'cute'),
(128, 'CV01'),
(40, 'dragon'),
(96, 'edgar allan poe'),
(95, 'edogawa ranpo'),
(62, 'face study'),
(5, 'fanart'),
(43, 'fantasy'),
(77, 'floating buildings'),
(71, 'flowers'),
(112, 'football'),
(48, 'forest'),
(44, 'friends'),
(130, 'Gawr Gura'),
(134, 'genshin impact'),
(121, 'ghibli'),
(8, 'girl'),
(10, 'glasses'),
(75, 'gold'),
(78, 'graphic novel'),
(115, 'grayscale'),
(38, 'green'),
(81, 'grey'),
(138, 'guys'),
(97, 'haikyuu!!'),
(90, 'happy'),
(110, 'hat'),
(125, 'hatsune miku'),
(117, 'hololive'),
(84, 'jalur gemilang'),
(106, 'Kim Hye-sung'),
(107, 'Kunigami Rensuke'),
(34, 'lighting'),
(2, 'logo'),
(131, 'long hair'),
(74, 'main character'),
(86, 'merdeka'),
(127, 'miku hatsune'),
(79, 'monochrome'),
(33, 'mountains'),
(120, 'my neighbour totoro'),
(83, 'mythical'),
(111, 'Nagi Seishirō'),
(85, 'national day'),
(23, 'night time'),
(19, 'night view'),
(66, 'nijisanji'),
(116, 'Ninomae Ina\'nis'),
(133, 'noelle'),
(20, 'object study'),
(35, 'orange'),
(12, 'orange background'),
(15, 'original art'),
(60, 'original character'),
(137, 'people'),
(59, 'pink'),
(65, 'portrait'),
(54, 'pose'),
(13, 'pose study'),
(73, 'princess'),
(6, 'purple'),
(101, 'realistic'),
(9, 'red'),
(58, 'reference'),
(72, 'romance'),
(124, 'school'),
(122, 'school girl'),
(123, 'school rebel'),
(49, 'school uniform'),
(104, 'seiyuu'),
(28, 'shoe study'),
(51, 'shoes'),
(53, 'sitting'),
(135, 'skadi'),
(57, 'sketching'),
(32, 'sky'),
(87, 'speedpaint'),
(18, 'stars'),
(70, 'streamer'),
(39, 'sun rays'),
(31, 'sunset'),
(14, 'teenager'),
(140, 'test'),
(119, 'totoro'),
(7, 'transparent'),
(92, 'travel'),
(46, 'trees'),
(93, 'tsukishima kei'),
(103, 'Uchiyama Kouki'),
(37, 'valley'),
(36, 'view'),
(126, 'vocaloid'),
(129, 'voicebank'),
(67, 'vtuber'),
(68, 'watarai hibari'),
(114, 'watercolour'),
(69, 'white'),
(136, 'woman'),
(94, 'yamaguchi tadashi'),
(64, 'yellow'),
(132, 'young lady'),
(100, 'young man');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `profile_banner` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notifications` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `display_name`, `username`, `password`, `profile_picture`, `profile_banner`, `bio`, `created_at`, `updated_at`, `notifications`) VALUES
(1, 'tenyukinim@gmail.com', 'Nim Mi', 'nimmi87', 'Nimmi876', 'uploads/profile_pictures/66744f6ca37e8.png', 'uploads/profile_banners/66744f6ca3e24.jpg', 'hello world', '2024-06-20 23:49:00', '2024-06-20 23:49:00', 1),
(2, 'rigleenim@gmail.com', 'Chula C', 'oolington', 'chulacamelia!1', 'uploads/profile_pictures/6675aff01eab3.jpg', 'uploads/profile_banners/6675aff01ed89.jpg', 'chula\'s art archive, welcome', '2024-06-22 00:53:04', '2024-06-25 01:07:26', 1),
(3, 'otakusensei811@gmail.com', 'Ain B', 'xerenaeey', 'ainbasirah!1', 'uploads/profile_pictures/6676925dac633.jpg', 'uploads/profile_banners/6676925dac9f7.jpg', 'commissions are currently closed', '2024-06-22 16:59:09', '2024-06-25 01:07:43', 1),
(4, 'farhana@mail.com', 'N Farhana', 'keinodino', 'dinokeino!1', 'uploads/profile_pictures/6677ebae6b357.jpg', 'uploads/profile_banners/6677ebae6bce8.jpg', 'hello, nice to meet everyone', '2024-06-23 17:32:30', '2024-06-23 17:32:30', 1),
(5, 'azim@mail.com', 'Azim Z', 'nyanza_sketch', 'azim!1', 'uploads/profile_pictures/6678137d682db.png', 'uploads/profile_banners/6678078c794e0.png', 'zuzu\r\nart account\r\nillustrator\r\ncommission closed\r\nlet\'s just say black and white is gud enough :)', '2024-06-23 19:31:24', '2024-06-23 23:48:02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `artwork_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `notification_type` enum('like','comment','follow') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`id`, `user_id`, `actor_id`, `artwork_id`, `comment_id`, `notification_type`, `created_at`) VALUES
(1, 2, 1, NULL, NULL, 'follow', '2024-06-22 22:59:44'),
(2, 3, 1, NULL, NULL, 'follow', '2024-06-22 23:21:56'),
(3, 1, 2, NULL, NULL, 'follow', '2024-06-22 23:39:31'),
(4, 2, 2, 6, NULL, 'like', '2024-06-22 23:40:42'),
(5, 2, 2, 6, 1, 'comment', '2024-06-22 23:41:01'),
(6, 2, 2, 6, NULL, 'like', '2024-06-22 23:41:23'),
(9, 3, 2, 19, NULL, 'like', '2024-06-22 23:45:02'),
(13, 2, 4, NULL, NULL, 'follow', '2024-06-23 18:38:22'),
(14, 1, 4, NULL, NULL, 'follow', '2024-06-23 18:38:26'),
(18, 5, 1, NULL, NULL, 'follow', '2024-06-23 23:08:46'),
(19, 4, 1, NULL, NULL, 'follow', '2024-06-23 23:09:04'),
(20, 5, 1, 40, NULL, 'like', '2024-06-23 23:09:37'),
(21, 5, 1, 40, 5, 'comment', '2024-06-23 23:09:47'),
(22, 2, 1, 6, NULL, 'like', '2024-06-23 23:10:16'),
(23, 2, 1, 6, 6, 'comment', '2024-06-23 23:10:36'),
(24, 3, 1, 22, NULL, 'like', '2024-06-23 23:19:43'),
(25, 2, 4, 14, NULL, 'like', '2024-06-24 00:13:51'),
(26, 2, 4, 6, NULL, 'like', '2024-06-24 00:14:19'),
(30, 2, 5, 5, NULL, 'like', '2024-07-16 23:59:32'),
(31, 2, 5, 10, NULL, 'like', '2024-07-16 23:59:43'),
(32, 2, 5, 12, NULL, 'like', '2024-07-16 23:59:58'),
(33, 3, 5, 23, NULL, 'like', '2024-07-17 00:00:11'),
(34, 3, 5, 19, NULL, 'like', '2024-07-17 00:00:21'),
(35, 3, 5, 15, NULL, 'like', '2024-07-17 00:00:27'),
(36, 5, 5, 40, 8, 'comment', '2024-07-17 00:00:46'),
(37, 4, 5, 31, NULL, 'like', '2024-07-17 00:01:12'),
(38, 4, 5, 29, NULL, 'like', '2024-07-17 00:01:20'),
(39, 4, 5, 27, NULL, 'like', '2024-07-17 00:01:27'),
(40, 3, 5, 24, NULL, 'like', '2024-07-17 00:01:32'),
(41, 4, 5, 35, NULL, 'like', '2024-07-17 00:01:44'),
(42, 5, 4, 44, NULL, 'like', '2024-07-17 00:02:27'),
(43, 5, 4, 41, NULL, 'like', '2024-07-17 00:02:41'),
(44, 5, 4, 39, NULL, 'like', '2024-07-17 00:02:54'),
(45, 5, 4, 37, NULL, 'like', '2024-07-17 00:03:01'),
(46, 3, 4, 24, NULL, 'like', '2024-07-17 00:03:37'),
(47, 3, 4, 21, NULL, 'like', '2024-07-17 00:03:51'),
(48, 3, 4, 19, NULL, 'like', '2024-07-17 00:04:04'),
(49, 3, 4, 17, NULL, 'like', '2024-07-17 00:04:17'),
(50, 2, 4, 9, NULL, 'like', '2024-07-17 00:04:32'),
(51, 2, 4, 13, NULL, 'like', '2024-07-17 00:04:39'),
(52, 2, 4, 5, NULL, 'like', '2024-07-17 00:04:44'),
(53, 5, 2, 39, NULL, 'like', '2024-07-17 00:05:20'),
(54, 5, 2, 36, NULL, 'like', '2024-07-17 00:05:33'),
(55, 5, 2, 44, NULL, 'like', '2024-07-17 00:05:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_replies`
--
ALTER TABLE `admin_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `artworks`
--
ALTER TABLE `artworks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `artwork_tags`
--
ALTER TABLE `artwork_tags`
  ADD PRIMARY KEY (`artwork_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `artwork_id` (`artwork_id`);

--
-- Indexes for table `contact_forms`
--
ALTER TABLE `contact_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`follower_id`,`following_id`),
  ADD KEY `following_id` (`following_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `artwork_id` (`artwork_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag_name` (`tag_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `actor_id` (`actor_id`),
  ADD KEY `artwork_id` (`artwork_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_replies`
--
ALTER TABLE `admin_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `artworks`
--
ALTER TABLE `artworks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contact_forms`
--
ALTER TABLE `contact_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_replies`
--
ALTER TABLE `admin_replies`
  ADD CONSTRAINT `admin_replies_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `contact_forms` (`id`),
  ADD CONSTRAINT `admin_replies_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`);

--
-- Constraints for table `artworks`
--
ALTER TABLE `artworks`
  ADD CONSTRAINT `artworks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `artwork_tags`
--
ALTER TABLE `artwork_tags`
  ADD CONSTRAINT `artwork_tags_ibfk_1` FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`),
  ADD CONSTRAINT `artwork_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`);

--
-- Constraints for table `contact_forms`
--
ALTER TABLE `contact_forms`
  ADD CONSTRAINT `contact_forms_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_notifications_ibfk_2` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_notifications_ibfk_3` FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`),
  ADD CONSTRAINT `user_notifications_ibfk_4` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
