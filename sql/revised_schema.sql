-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2026 at 03:24 AM
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
-- Database: `beyond_the_map`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_executive`
--

CREATE TABLE `account_executive` (
  `booking_id` int(11) NOT NULL,
  `ae_staff_id` int(11) DEFAULT NULL,
  `case_status` enum('processing','for_follow_up','ongoing','payment_issue','finished','refund') DEFAULT 'processing',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_executive`
--

INSERT INTO `account_executive` (`booking_id`, `ae_staff_id`, `case_status`, `created_at`, `updated_at`) VALUES
(1, 1, 'ongoing', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, 1, 'processing', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(3, 1, 'finished', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(4, 1, 'payment_issue', '2026-03-01 11:38:33', '2026-03-01 11:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `tour_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_status` enum('confirmed','reserved','cancelled','completed') DEFAULT 'reserved',
  `seat_number` varchar(30) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `payment_status` enum('paid','partial','pending','overdue') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_id`, `tour_id`, `booking_date`, `booking_status`, `seat_number`, `amount`, `payment_status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-03-02', 'confirmed', 'A-01', 25500.00, 'partial', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, 2, 2, '2026-03-06', 'reserved', 'B-03', 32000.00, 'pending', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(3, 3, 3, '2026-03-09', 'completed', 'C-02', 28000.00, 'paid', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(4, 4, 4, '2026-03-04', 'confirmed', 'D-05', 22000.00, 'overdue', '2026-03-01 11:38:33', '2026-03-01 11:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `crm_bookings`
--

CREATE TABLE `crm_bookings` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crm_bookings`
--

INSERT INTO `crm_bookings` (`id`, `customer_id`, `booking_id`, `completed`, `completed_at`, `created_at`) VALUES
(1, 1, 1, 0, NULL, '2026-03-01 11:38:33'),
(2, 2, 2, 0, NULL, '2026-03-01 11:38:33'),
(3, 3, 3, 1, '2026-02-24 14:09:19', '2026-03-01 11:38:33'),
(4, 4, 4, 0, NULL, '2026-03-01 11:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `crm_interactions`
--

CREATE TABLE `crm_interactions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `ae_staff_id` int(11) DEFAULT NULL,
  `interaction_type` enum('inquiry','follow_up','clarification','note','booking') NOT NULL,
  `details` text NOT NULL,
  `next_action_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `tier` enum('new','silver','gold','vip') DEFAULT 'new',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `full_name`, `email`, `phone`, `tier`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Vanessa Radaza', 'vanessa@mail.com', '09181110001', 'gold', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, NULL, 'Erick Taguba', 'erick@mail.com', '09181110002', 'silver', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(3, NULL, 'Rens Solano', 'rens@mail.com', '09181110003', 'vip', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(4, NULL, 'Maria Alvares', 'maria@mail.com', '09181110004', 'new', '2026-03-01 11:38:33', '2026-03-01 11:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `facility_name` varchar(150) NOT NULL,
  `facility_type` varchar(120) NOT NULL,
  `capacity` int(11) DEFAULT 0,
  `location` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `facility_name`, `facility_type`, `capacity`, `location`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Business Lounge A', 'Lounge', 40, 'Terminal 1', 1, '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, 'VIP Reception Suite', 'VIP', 12, 'Terminal 3', 1, '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(3, 'Arrival Assistance Desk', 'Assistance', 25, 'Terminal 2', 1, '2026-03-01 11:38:33', '2026-03-01 11:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `facility_coordination_status`
--

CREATE TABLE `facility_coordination_status` (
  `id` int(11) NOT NULL,
  `facility_reservation_id` int(11) NOT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `logistics_status` enum('queued','dispatched','en route','arrived','completed') NOT NULL DEFAULT 'queued',
  `completion_time` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facility_reservations`
--

CREATE TABLE `facility_reservations` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `status` enum('requested','approved','assigned','in progress','completed','cancelled') DEFAULT 'requested',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facility_reservations`
--

INSERT INTO `facility_reservations` (`id`, `booking_id`, `facility_id`, `reservation_date`, `priority`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-03-02', 'high', 'approved', 'VIP early check-in support', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, 2, 3, '2026-03-06', 'normal', 'requested', 'Wheelchair assistance request', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(3, 3, 2, '2026-03-09', 'normal', 'completed', 'Security escort included', '2026-03-01 11:38:33', '2026-03-01 11:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_role` enum('customer','ae','staff') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_role` enum('customer','ae','staff') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `module_origin` varchar(100) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `module_source` varchar(100) NOT NULL,
  `related_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passport_applications`
--

CREATE TABLE `passport_applications` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `passport_number` varchar(50) DEFAULT NULL,
  `country` varchar(120) DEFAULT NULL,
  `documents_status` enum('approved','submitted','missing','rejected','not started') DEFAULT 'not started',
  `application_status` enum('visa issued','approved','processing','under review','pending','action required','not started') DEFAULT 'not started',
  `priority` enum('low','medium','high') DEFAULT 'low',
  `submission_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passport_applications`
--

INSERT INTO `passport_applications` (`id`, `booking_id`, `passport_number`, `country`, `documents_status`, `application_status`, `priority`, `submission_date`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 'DAS2301A', 'Japan', 'approved', 'processing', 'medium', '2026-02-15', 'Waiting embassy slot', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, 2, 'IN1209B', 'France', 'submitted', 'under review', 'high', '0000-00-00', 'Missing bank certificate', '2026-03-01 11:38:33', '2026-03-02 22:46:20'),
(3, 3, 'REV8765C', 'Canada', 'missing', 'visa issued', 'low', '0000-00-00', 'Completed', '2026-03-01 11:38:33', '2026-03-02 22:51:44'),
(4, 4, 'EXP5533D', 'Australia', 'submitted', 'processing', 'high', '0000-00-00', 'Under evaluation', '2026-03-01 11:38:33', '2026-03-02 22:51:07');

-- --------------------------------------------------------

--
-- Table structure for table `passport_documents`
--

CREATE TABLE `passport_documents` (
  `id` int(11) NOT NULL,
  `passport_application_id` int(11) NOT NULL,
  `document_type` varchar(120) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('submitted','approved','missing','rejected') DEFAULT 'submitted',
  `reviewed_by_staff_id` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passport_documents`
--

INSERT INTO `passport_documents` (`id`, `passport_application_id`, `document_type`, `file_path`, `status`, `reviewed_by_staff_id`, `reviewed_at`, `created_at`) VALUES
(1, 1, 'Passport Bio Page', 'uploads/passport_1_bio.pdf', 'approved', 2, '2026-02-16 10:00:00', '2026-03-01 11:38:33'),
(2, 2, 'Bank Certificate', 'uploads/passport_2_bank.pdf', 'missing', 2, '2026-02-19 10:00:00', '2026-03-01 11:38:33'),
(3, 3, 'Visa Form', 'uploads/passport_3_form.pdf', 'approved', 2, '2026-02-07 09:00:00', '2026-03-01 11:38:33'),
(4, 4, 'Photo ID', 'uploads/passport_4_photo.pdf', 'submitted', NULL, NULL, '2026-03-01 11:38:33'),
(5, 2, 'Passport Image', 'uploads/passport_2.jpg', 'submitted', NULL, NULL, '2026-03-03 17:07:51'),
(6, 2, '1x1 Photo', 'uploads/photo_2.jpg', 'submitted', NULL, NULL, '2026-03-03 17:07:51');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `status` enum('paid','pending','partial','overdue','cancelled') DEFAULT 'pending',
  `reference_no` varchar(80) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount`, `due_date`, `paid_at`, `status`, `reference_no`, `created_at`, `updated_at`) VALUES
(1, 1, 25500.00, '2026-02-28', NULL, 'partial', 'PAY-0001', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, 2, 32000.00, '2026-02-27', NULL, 'pending', 'PAY-0002', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(3, 3, 28000.00, '2026-02-24', '2026-02-24 14:09:19', 'paid', 'PAY-0003', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(4, 4, 22000.00, '2026-02-23', NULL, 'overdue', 'PAY-0004', '2026-03-01 11:38:33', '2026-03-01 11:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `payment_reminders`
--

CREATE TABLE `payment_reminders` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `reminder_type` enum('email','sms','in_app') NOT NULL DEFAULT 'in_app',
  `reminder_status` enum('queued','sent','failed') NOT NULL DEFAULT 'queued',
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_profiles`
--

CREATE TABLE `staff_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `team` enum('account_executive','passport','facilities','logistics','finance','admin') NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_profiles`
--

INSERT INTO `staff_profiles` (`id`, `user_id`, `full_name`, `team`, `phone`, `created_at`, `updated_at`) VALUES
(1, 2, 'Maria Santos', 'account_executive', '09171230001', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, 3, 'John Reyes', 'passport', '09171230002', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(3, 4, 'Anne Cruz', 'facilities', '09171230003', '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(4, 5, 'Mike Dela Rosa', 'finance', '09171230004', '2026-03-01 11:38:33', '2026-03-01 11:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `tour_name` varchar(180) NOT NULL,
  `destination` varchar(150) NOT NULL,
  `rate` decimal(12,2) DEFAULT 0.00,
  `capacity` int(11) DEFAULT 0,
  `departure_time` time DEFAULT NULL,
  `tour_date` date NOT NULL,
  `status` enum('open','closed','full','upcoming','limited') DEFAULT 'open',
  `duration_days` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tours`
--

INSERT INTO `tours` (`id`, `tour_name`, `destination`, `rate`, `capacity`, `departure_time`, `tour_date`, `status`, `duration_days`, `created_at`, `updated_at`) VALUES
(1, 'Kyoto Cultural Walk', 'Kyoto, Japan', 25500.00, 30, '08:30:00', '2026-03-02', 'open', 5, '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, 'Paris City Escape', 'Paris, France', 32000.00, 25, '10:00:00', '2026-03-06', 'limited', 6, '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(3, 'Sydney Coastal Adventure', 'Sydney, Australia', 28000.00, 20, '13:30:00', '2026-03-09', 'open', 4, '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(4, 'Dubai Desert Experience', 'Dubai, UAE', 22000.00, 18, '15:00:00', '2026-03-04', 'upcoming', 3, '2026-03-01 11:38:33', '2026-03-01 11:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','ae','staff','customer') NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `email`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin123', 'admin', 'admin@beyondthemap.local', 1, '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(2, 'ae.maria', 'ae123', 'ae', 'ae.maria@beyondthemap.local', 1, '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(3, 'passport.john', 'staff123', 'staff', 'passport.john@beyondthemap.local', 1, '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(4, 'facilities.anne', 'staff123', 'staff', 'facilities.anne@beyondthemap.local', 1, '2026-03-01 11:38:33', '2026-03-01 11:38:33'),
(5, 'finance.mike', 'staff123', 'staff', 'finance.mike@beyondthemap.local', 1, '2026-03-01 11:38:33', '2026-03-01 11:38:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_executive`
--
ALTER TABLE `account_executive`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `ae_staff_id` (`ae_staff_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `tour_id` (`tour_id`);

--
-- Indexes for table `crm_bookings`
--
ALTER TABLE `crm_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `crm_interactions`
--
ALTER TABLE `crm_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_crm_interactions_customer` (`customer_id`),
  ADD KEY `fk_crm_interactions_staff` (`ae_staff_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facility_coordination_status`
--
ALTER TABLE `facility_coordination_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_facility_coordination_reservation` (`facility_reservation_id`),
  ADD KEY `idx_facility_coordination_status` (`logistics_status`),
  ADD KEY `fk_facility_coordination_staff` (`assigned_staff_id`);

--
-- Indexes for table `facility_reservations`
--
ALTER TABLE `facility_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_guests_customer` (`customer_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_receiver` (`receiver_role`,`receiver_id`,`is_read`),
  ADD KEY `idx_messages_created` (`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_read` (`is_read`),
  ADD KEY `idx_notifications_module` (`module_source`),
  ADD KEY `idx_notifications_created` (`created_at`);

--
-- Indexes for table `passport_applications`
--
ALTER TABLE `passport_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `passport_documents`
--
ALTER TABLE `passport_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `passport_application_id` (`passport_application_id`),
  ADD KEY `reviewed_by_staff_id` (`reviewed_by_staff_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_reminders_payment` (`payment_id`);

--
-- Indexes for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `crm_bookings`
--
ALTER TABLE `crm_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `crm_interactions`
--
ALTER TABLE `crm_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `facility_coordination_status`
--
ALTER TABLE `facility_coordination_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facility_reservations`
--
ALTER TABLE `facility_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `passport_applications`
--
ALTER TABLE `passport_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `passport_documents`
--
ALTER TABLE `passport_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_executive`
--
ALTER TABLE `account_executive`
  ADD CONSTRAINT `account_executive_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_executive_ibfk_2` FOREIGN KEY (`ae_staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `crm_bookings`
--
ALTER TABLE `crm_bookings`
  ADD CONSTRAINT `crm_bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `crm_bookings_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `crm_interactions`
--
ALTER TABLE `crm_interactions`
  ADD CONSTRAINT `fk_crm_interactions_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_crm_interactions_staff` FOREIGN KEY (`ae_staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `facility_coordination_status`
--
ALTER TABLE `facility_coordination_status`
  ADD CONSTRAINT `fk_facility_coordination_reservation` FOREIGN KEY (`facility_reservation_id`) REFERENCES `facility_reservations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_facility_coordination_staff` FOREIGN KEY (`assigned_staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `facility_reservations`
--
ALTER TABLE `facility_reservations`
  ADD CONSTRAINT `facility_reservations_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `facility_reservations_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `guests`
--
ALTER TABLE `guests`
  ADD CONSTRAINT `fk_guests_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `passport_applications`
--
ALTER TABLE `passport_applications`
  ADD CONSTRAINT `passport_applications_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `passport_documents`
--
ALTER TABLE `passport_documents`
  ADD CONSTRAINT `passport_documents_ibfk_1` FOREIGN KEY (`passport_application_id`) REFERENCES `passport_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `passport_documents_ibfk_2` FOREIGN KEY (`reviewed_by_staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD CONSTRAINT `fk_payment_reminders_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD CONSTRAINT `staff_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
