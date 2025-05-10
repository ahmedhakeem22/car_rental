-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 10 مايو 2025 الساعة 15:35
-- إصدار الخادم: 8.0.37
-- PHP Version: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cars`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','manager','staff') DEFAULT 'staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`) VALUES
(1, 'ahmed', '$2y$10$TBAAibYs3dVaeIxaLHXEceUgLel8AZmYs7u7pzuoo5YmMcwcYjbsC', 'superadmin');

-- --------------------------------------------------------

--
-- بنية الجدول `cars`
--

CREATE TABLE `cars` (
  `id` int NOT NULL,
  `model` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `year` int DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `description` text,
  `is_available` tinyint(1) DEFAULT '1',
  `pickup_location_name` varchar(255) DEFAULT NULL COMMENT 'Descriptive name for pickup location',
  `pickup_latitude` decimal(10,8) DEFAULT NULL COMMENT 'Latitude of pickup location',
  `pickup_longitude` decimal(11,8) DEFAULT NULL COMMENT 'Longitude of pickup location',
  `average_rating` decimal(3,2) DEFAULT NULL,
  `total_reviews` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- إرجاع أو استيراد بيانات الجدول `cars`
--

INSERT INTO `cars` (`id`, `model`, `brand`, `year`, `price_per_day`, `image`, `quantity`, `description`, `is_available`, `pickup_location_name`, `pickup_latitude`, `pickup_longitude`, `average_rating`, `total_reviews`) VALUES
(1, 'Mustang', 'Ford', 2024, 100.00, 'carimg_edit_681e877667b8b6.30031870.jpeg', 0, 'The Ford Mustang is an iconic American muscle car known for its powerful performance and bold design. First introduced in 1964, it has become a symbol of freedom and speed. The Mustang offers a range of engines, from efficient turbocharged options to high-performance V8s. With its aggressive styling, advanced technology, and thrilling driving experience, the Ford Mustang continues to be a favorite among car enthusiasts worldwide.', 0, NULL, NULL, NULL, NULL, 0),
(2, 'Civic', 'Honda', 2025, 100.00, 'carimg_edit_681e871b5322f0.37867843.jpg', 1, 'While the Civic is sold in largely the same form worldwide, differences in the name of the models exist between markets. In Japan, the hatchback Civic is just called \"Civic\" while the sedan model was called the Civic Ferio ( [ja]) during the fifth to seventh generations. The sixth-generation sedan was also sold as the Integra SJ. In Europe and the United States, \"Civic\" generically refers to any model, though in Europe the coupe is branded the \"Civic Coupe\". A four-door station wagon model called the Civic Shuttle (also Civic Pro in Japan) was available from 1984 until 1991 (this brand name would later be revived for the mid-1990s Honda Shuttle people carrier, known in some markets as the Honda Odyssey). In South Africa, the sedan (the only model sold there until the 1996 launch of the sixth generation sedan and hatch) was known as the Ballade. Other models have been built on the Civic platform, including Prelude, Ballade, CR-X, Quint, Concerto, Domani, CR-X Del Sol, Integra, and CR-V.\r\n\r\nAlso, at various times, the Civic or Civic-derived models have been sold by marques other than Honda – for example, Rover sold the 200, 400 and 45, each of which were Civic-based at some point (first 200s were the second generation Ballade; from 1990 the 200 and 400 were based on the Concerto; the 400 was the 1995 Domani), as was their predecessor, the Triumph Acclaim, based on the first Honda Ballade. The Honda Domani, an upscale model based on the Civic, was sold as the Isuzu Gemini in Japan (1992–2000), and confusingly the 5-door Domani was sold as the Honda Civic (along with the \"real\" hatchback and sedan Civics) in Europe from 1995 to 2000. In Thailand, the sixth generation Civic was available as the four-door Isuzu Vertex. The sixth-generation station wagon was sold as the Honda Orthia, with the Partner as the downmarket commercial variant. The seventh generation minivan model is called the Honda Stream. In Canada, the sixth and seventh generation Civics were mildly redesigned to create the Acura EL until the advent of the eight generation Civic, which was used to create the Acura CSX, which was designed in Canada. Honda Japan adopted the CSX styling for the Civic in its home country.\r\n\r\nThe three-door hatchback body style has been somewhat unpopular in the United States, but has achieved wide acceptance in Canada, as well as popularity in Japan and European markets, helping cement Honda\'s reputation as a maker of sporty compact models. Starting in 2002, the Civic three-door hatchback has been built exclusively at Honda\'s manufacturing plant in Swindon, England[44] – previously the five-door Civic/Domani and the Civic Aerodeck (based on the Japanese Orthia) were built in this plant for sale in Europe along with the Japanese Civics. Accordingly, all instances of the current model (left or right hand drive, anywhere in the world) are British-made cars designed with Japanese engineering, except for the US-built two-door coupe and the sedan version built in Brazil for the Latin American market.\r\n\r\nIn North America, the Civic hatchback was dropped for 2006. The 2006 model year standard Civics for North America are manufactured in Alliston, Ontario, Canada (sedans, coupes and Si Coupes) and East Liberty, Ohio (sedans), while the Hybrid version is manufactured in Japan.\r\n\r\nIn Brazil, although being considered for local manufacturing since the early 1980s (it was illegal to import cars in Brazil from 1973 until 1990), the Civic wasn\'t available until 1992, via official importing. In 1997, production of the sixth generation Civic sedan started in the Sumaré (a city near Campinas, in the state of São Paulo) factory. The only differences between the Japanese model and the Brazilian model were a slightly higher ground clearance because of the country\'s road conditions and adaptations to make the engine suitable to Brazilian commercial gasoline, which contains about 25% ethanol (E25), and the absence of sunroof in the Brazilian sixth generation Civic EX. The seventh generation production started in 2001, displacing the Chevrolet Vectra from the top sales record for the mid-size sedan segment, however it lost that position to the Toyota Corolla the following year. In 2006, the eighth generation was released and regained the sales leadership. Identical to the North American version, it lacks options such as a moonroof, and standard security equipment like VSA, and side and curtain airbags which were removed because of a lack of car safety laws in the Mercosur. Furthermore, the Brazilian subsidiary began producing flex-fuel versions for the Civic and the Fit models, capable of running on any blend of gasoline (E20 to E25 blend in Brazil) and ethanol up to E100.[45]', 1, NULL, NULL, NULL, NULL, 0),
(3, 'Corolla', 'Toyota', 2025, 100.00, 'carimg_edit_681e86ba8576b8.98727122.png', 10, 'The 2024 Toyota Corolla is considered one of the best cars in its class. It\'s a sedan with a significant fuel economy in the commercial market, clearly amplified by its distinctive design and efficient performance. It\'s also one of the most economical cars in terms of profitability, making it preferable to own a car that doesn\'t contribute to its components.\r\n\r\nThe 2024 Toyota Corolla\'s exterior design is luxurious, featuring clear lines on the sides and LED headlights, giving it a stunning touch. The high-beam headlights also help you see better and ensure safer driving.\r\n\r\nThe 2024 Corolla\'s interior also boasts numerous features, including velvet seats for greater passenger comfort. The seats can be adjusted in multiple positions, in addition to a modern design and advanced technologies, such as an entertainment system with an easy-to-use interface and other technologies like CarPlay and Android Auto.', 1, '6458 ابن ابي قرة, Riyadh, Riyadh, Saudi Arabia', 24.60267400, 46.74986400, 5.00, 1),
(4, 'sfdgasrg', 'ff', 2025, 300.00, 'carimg_681f6e9038fcd0.68892864.jpg', 1, '', 1, '2834 عبدالرحمن بن قاسم, Riyadh, Riyadh, Saudi Arabia', 24.51861200, 46.67382700, NULL, 0);

-- --------------------------------------------------------

--
-- بنية الجدول `rentals`
--

CREATE TABLE `rentals` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `car_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('booked','completed','cancelled') DEFAULT 'booked',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- إرجاع أو استيراد بيانات الجدول `rentals`
--

INSERT INTO `rentals` (`id`, `user_id`, `car_id`, `start_date`, `end_date`, `total_price`, `status`, `payment_status`, `created_at`) VALUES
(1, 1, 1, '2025-05-13', '2025-05-28', 1500.00, 'booked', 'paid', '2025-05-09 22:23:48'),
(2, 1, 3, '2025-05-11', '2025-05-27', 1600.00, 'completed', 'paid', '2025-05-09 23:09:34');

-- --------------------------------------------------------

--
-- بنية الجدول `returns`
--

CREATE TABLE `returns` (
  `id` int NOT NULL,
  `rental_id` int NOT NULL,
  `return_date` date NOT NULL,
  `condition_notes` text,
  `late_fee` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `car_id` int NOT NULL,
  `rental_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `likes_count` int NOT NULL DEFAULT '0',
  `dislikes_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- إرجاع أو استيراد بيانات الجدول `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `car_id`, `rental_id`, `rating`, `comment`, `likes_count`, `dislikes_count`, `created_at`) VALUES
(1, 1, 3, 2, 5, '', 1, 0, '2025-05-10 13:52:45');

-- --------------------------------------------------------

--
-- بنية الجدول `review_votes`
--

CREATE TABLE `review_votes` (
  `id` int NOT NULL,
  `review_id` int NOT NULL,
  `user_id` int NOT NULL,
  `vote_type` enum('like','dislike') NOT NULL,
  `voted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- إرجاع أو استيراد بيانات الجدول `review_votes`
--

INSERT INTO `review_votes` (`id`, `review_id`, `user_id`, `vote_type`, `voted_at`) VALUES
(1, 1, 1, 'like', '2025-05-10 13:58:55');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `created_at`, `security_question`, `security_answer`) VALUES
(1, 'sdfvsdfvsdvsdf', 'ahmedhakeem2222@gmail.com', NULL, '$2y$10$TBAAibYs3dVaeIxaLHXEceUgLel8AZmYs7u7pzuoo5YmMcwcYjbsC', '2025-05-06 20:59:09', 'What is your favorite color?', 'green'),
(2, 'ahmed hakeem', 'ahmedha2keem2222@gmail.com', NULL, '$2y$10$mOiZe5JEtTkx0o/1uBIimezKPdv997NMVWsZWaIJsSb3vQ7rRPQ9O', '2025-05-07 21:01:20', 'What is your favorite color?', 'green');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`rental_id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `review_votes`
--
ALTER TABLE `review_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_review_vote` (`user_id`,`review_id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_votes`
--
ALTER TABLE `review_votes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);

--
-- قيود الجداول `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`);

--
-- قيود الجداول `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`);

--
-- قيود الجداول `review_votes`
--
ALTER TABLE `review_votes`
  ADD CONSTRAINT `review_votes_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
