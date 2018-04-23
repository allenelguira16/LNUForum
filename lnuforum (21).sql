-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2018 at 04:20 PM
-- Server version: 10.1.28-MariaDB
-- PHP Version: 7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lnuforum`
--

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

CREATE TABLE `answer` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `answer_content` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `answer`
--

INSERT INTO `answer` (`id`, `topic_id`, `user_id`, `answer_content`, `date`) VALUES
(1, 1, '9', 'Hey!', '2018-04-22 16:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `user_id1` text NOT NULL,
  `user_id2` text NOT NULL,
  `message` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`id`, `user_id1`, `user_id2`, `message`, `date`) VALUES
(1, '1', '2', 'qwe', '2018-04-23 13:37:10'),
(2, '1', '2', 'qweqwe', '2018-04-23 13:37:10'),
(3, '1', '2', 'Sad', '2018-04-23 13:49:38'),
(4, '1', '6', 'sad', '2018-04-23 13:49:52');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` int(11) NOT NULL,
  `course` text NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`id`, `course`, `description`) VALUES
(1, 'CICS', 'College of Information and Computing Studies');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `like_id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `ref_id` int(11) NOT NULL,
  `ref` text NOT NULL,
  `status` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `moderators`
--

CREATE TABLE `moderators` (
  `id` int(11) NOT NULL,
  `user_id` text NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `notif`
--

CREATE TABLE `notif` (
  `id` int(11) NOT NULL,
  `user_id` text NOT NULL,
  `from_id` text NOT NULL,
  `table_from` text NOT NULL,
  `table_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notif`
--

INSERT INTO `notif` (`id`, `user_id`, `from_id`, `table_from`, `table_id`, `date_created`, `status`) VALUES
(1, '1', '9', 'topic', 1, '2018-04-22 16:07:15', 'answer'),
(2, '1', '9', 'topic', 1, '2018-04-23 11:28:49', 'like'),
(3, '1', '9', 'topic', 1, '2018-04-23 11:39:28', 'like'),
(4, '1', '9', 'topic', 1, '2018-04-23 11:39:32', 'like'),
(5, '1', '9', 'topic', 1, '2018-04-23 11:39:39', 'unlike'),
(6, '1', '9', 'topic', 1, '2018-04-23 11:39:43', 'like'),
(7, '1', '9', 'topic', 1, '2018-04-23 11:44:30', 'unlike');

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `user_id`, `filename`, `type`) VALUES
(1, 9, 'profile-pic/9/2866aa19f6124ad95a48189f4099045f.png', 'primary');

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `sess_id` text NOT NULL,
  `session_timeout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `subject` text NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`id`, `course_id`, `subject`, `description`) VALUES
(1, 1, 'DBMS', 'Database Management System');

-- --------------------------------------------------------

--
-- Table structure for table `topic`
--

CREATE TABLE `topic` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic_title` varchar(255) NOT NULL,
  `topic` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `tags` text NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_likes` int(11) NOT NULL,
  `views` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `topic`
--

INSERT INTO `topic` (`id`, `subject_id`, `course_id`, `user_id`, `topic_title`, `topic`, `type`, `tags`, `date`, `total_likes`, `views`) VALUES
(1, 1, 1, 1, 'Sample', 'emojis<br><img alt=\"emoji\" src=\"http://localhost/LNUForum/includes/php/emoji/emojiv2/Smiling.png\" class=\"emoji\"> <img alt=\"emoji\" src=\"http://localhost/LNUForum/includes/php/emoji/emojiv2/Slightly_Smiling_Emoji_Icon.png\" class=\"emoji\"> <img alt=\"emoji\" src=\"http://localhost/LNUForum/includes/php/emoji/emojiv2/Super_Angry_Face_Emoji_ios10.png\" class=\"emoji\"> <img alt=\"emoji\" src=\"http://localhost/LNUForum/includes/php/emoji/emojiv2/Heart_Eyes_Emoji_2.png\" class=\"emoji\"> <br>BBCODES<br><b>Bold</b><br><i>Italic</i><br><u>underline</u><br><a href=\"http://goole.com\" target=\"_blank\" style=\"text-decoration:underline; color: blue;\">Google.com</a><br><b><i><u><a href=\"http://google.com\" target=\"_blank\" style=\"text-decoration:underline; color: blue;\"><span style=\"color:red; font-size:12px; font-family:Comic Sans MS; \">Hello World</span>\n</a></u></i></b>', 'topic', '0', '2018-04-18 22:48:22', 0, 415),
(2, 1, 1, 9, 'Hello World!', 'Hello my Friend', '', 'aaa', '2018-04-23 18:00:27', 0, 0),
(3, 1, 1, 9, 'BBCODES', '[b]Bold[/b] [i]Itallic[/i]', '', 'Hey!', '2018-04-23 18:02:07', 0, 0),
(4, 1, 1, 9, 'Hey', 'Hey', '', 'Hey!', '2018-04-23 18:02:32', 0, 0),
(5, 1, 1, 9, 'Hey', 'Hey', '', 'Hey!', '2018-04-23 18:02:33', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` text NOT NULL,
  `user_type` varchar(7) NOT NULL,
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `pass` text NOT NULL,
  `course` varchar(255) NOT NULL,
  `year` int(4) NOT NULL,
  `email` varchar(255) NOT NULL,
  `bdate` date NOT NULL,
  `date_created` timestamp(1) NOT NULL DEFAULT CURRENT_TIMESTAMP(1) ON UPDATE CURRENT_TIMESTAMP(1),
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `verified` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `user_type`, `fname`, `lname`, `pass`, `course`, `year`, `email`, `bdate`, `date_created`, `status`, `verified`) VALUES
(1, 'AllenElguira16', 'admin', 'Michael Allen', 'Elguira', '$2y$10$su2PkRD7vjGVyrT3n5RaLOl4NmltJrTc623e3EpJK/ViPLYB/Pxfq', '', 0, 'fbwar98@gmail.com', '0000-00-00', '2018-04-21 13:03:50.0', 0, 0),
(2, 'Taki_Tachibana', 'Student', 'Taki', 'Tachibana', '$2y$10$MA0b7UUXz42q6Z7PeJ3KAuRTualyxqkIfcAZh5uLhPPRI7/Un9P5C', 'CICS', 1, 'Taki_Tachibana@gmail.com', '0000-00-00', '2018-04-22 15:00:52.0', 0, 0),
(3, 'Mitsuha_Miyamizu', 'Student', 'Mitsuha', 'Miyamizu', '$2y$10$NKHPxxO2l98jhogExckOSe4MiIRxGiHBBmja6dtx50bm7zoQjBLDW', 'CICS', 4, 'Mitsuha_Miyamizu@gmail.com', '0000-00-00', '2018-04-22 15:00:57.4', 0, 0),
(6, 'niyognabaog16', '', 'Allen', 'Elguira', '$2y$10$M1YRMiPg2HurQoE/ewT8Oula54bZedZIMeTDJ6OpPExN/jRd/pe3S', 'CICS', 3, 'fbwar98@gmail.com', '0000-00-00', '2018-04-22 15:01:00.8', 0, 0),
(7, 'user_num1', '', 'user', 'pass', '$2y$10$lBHDzBlLzW63trs9N.bZ6Oz6hwByT9WjtCG20QwSrmWerR9prZA3a', 'CICS', 4, 'fbwar98@gmail.com', '0000-00-00', '2018-04-22 15:01:03.8', 0, 0),
(9, 'Syntaxxxx_Error', '', 'Allen', 'Elguira', '$2y$10$BFrjNxWuXJuHwzGoaufJoOxql3GbFIn9NRFfaVHyTTEcXt15RX4..', 'CICS', 3, 'fbwar98@gmail.com', '0000-00-00', '2018-04-22 19:16:33.5', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `verification`
--

CREATE TABLE `verification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hash` text NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answer_id` (`answer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_id`),
  ADD KEY `thread_id` (`ref_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `moderators`
--
ALTER TABLE `moderators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notif`
--
ALTER TABLE `notif`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_ibfk_1` (`user_id`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `verification`
--
ALTER TABLE `verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answer`
--
ALTER TABLE `answer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `moderators`
--
ALTER TABLE `moderators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notif`
--
ALTER TABLE `notif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `session`
--
ALTER TABLE `session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `topic`
--
ALTER TABLE `topic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `verification`
--
ALTER TABLE `verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `answer_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topic` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`answer_id`) REFERENCES `answer` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `subject_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`);

--
-- Constraints for table `topic`
--
ALTER TABLE `topic`
  ADD CONSTRAINT `topic_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `topic_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `verification`
--
ALTER TABLE `verification`
  ADD CONSTRAINT `verification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
