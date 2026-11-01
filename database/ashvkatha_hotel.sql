-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ashvkatha_hotel
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `ashvkatha_hotel`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `ashvkatha_hotel` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `ashvkatha_hotel`;

--
-- Table structure for table `booking_services`
--

DROP TABLE IF EXISTS `booking_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `booking_services_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `booking_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_services`
--

LOCK TABLES `booking_services` WRITE;
/*!40000 ALTER TABLE `booking_services` DISABLE KEYS */;
INSERT INTO `booking_services` VALUES (4,6,1,1,120.00,120.00),(5,6,2,1,75.00,75.00),(6,7,3,1,95.00,95.00),(7,8,1,1,120.00,120.00),(8,8,3,1,95.00,95.00),(9,8,4,1,60.00,60.00),(10,9,1,1,120.00,120.00),(11,9,2,1,75.00,75.00),(12,10,1,1,120.00,120.00),(14,17,1,1,120.00,120.00),(15,17,2,1,75.00,75.00);
/*!40000 ALTER TABLE `booking_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_number` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `adults` int(11) NOT NULL DEFAULT 1,
  `children` int(11) NOT NULL DEFAULT 0,
  `total_guests` int(11) NOT NULL DEFAULT 1,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','checked_in','checked_out','cancelled','rejected') NOT NULL DEFAULT 'confirmed',
  `cancellation_reason` text DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_number` (`booking_number`),
  KEY `room_id` (`room_id`),
  KEY `idx_booking_dates` (`check_in`,`check_out`),
  KEY `idx_booking_status` (`status`),
  KEY `idx_booking_user` (`user_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (4,'ASHV-20261101-5A4C',2,1,'2026-11-01','2026-11-02',2,0,2,168.00,'confirmed',NULL,'','2026-11-01 14:47:28'),(5,'ASHV-20261101-F7F8',2,2,'2026-11-01','2026-11-02',2,0,2,168.00,'confirmed',NULL,'','2026-11-01 14:47:50'),(6,'ASHV-2BF0AC',2,1,'2026-11-04','2026-11-07',2,0,2,699.00,'confirmed',NULL,'Quiet corner room preferred. Celebrating wedding anniversary.','2026-10-29 18:30:00'),(7,'ASHV-2C3208',2,5,'2026-11-13','2026-11-16',2,1,3,834.20,'cancelled',NULL,'Late check-in anticipated around 18:00 from Keshod airport.','2026-11-07 18:30:00'),(8,'ASHV-2C4460',2,21,'2026-10-18','2026-10-21',4,2,6,4307.00,'checked_out',NULL,'Private butler service and heated plunge pool setup requested.','2026-10-12 18:30:00'),(9,'ASHV-2C6F1F',6,17,'2026-10-27','2026-11-02',2,0,2,4563.00,'checked_out',NULL,'Botanical herbal bath therapy scheduled on arrival.','2026-10-21 18:30:00'),(10,'ASHV-2C84D1',6,9,'2026-10-02','2026-10-05',2,0,2,1195.20,'checked_out',NULL,'Sattvic organic dietary request for both guests.','2026-09-26 18:30:00'),(11,'ASHV-20261101-3DD8',2,3,'2026-11-01','2026-11-02',2,0,2,172.65,'checked_in',NULL,'','2026-11-01 15:13:08'),(12,'ASHV-20261101-A090',2,4,'2026-11-01','2026-11-02',2,0,2,172.65,'cancelled',NULL,'','2026-11-01 15:13:21'),(13,'ASHV-20261101-24DF',2,5,'2026-11-01','2026-11-02',2,0,2,253.22,'cancelled',NULL,'','2026-11-01 15:14:09'),(17,'ASHV-20261101-FCC9',2,9,'2026-11-11','2026-11-13',2,0,2,960.25,'confirmed',NULL,'Test booking with curated experiences add-on','2026-11-01 16:53:26');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (3,'Purvjeet Gida','aminop@gmail.com','','Room Reservation Inquiry','dfbv',0,'2026-11-01 14:50:33'),(4,'Vikramaditya Singhania','v.singhania@heritageindia.com','+91 98220 55443','Private Retreat & Wedding Inquiry','We are considering Ashvkatha for an intimate 3-day wellness and wedding celebration for 35 guests in late autumn. Could your concierge team advise on booking all 24 sanctuaries exclusively?',0,'2026-11-01 08:30:34'),(5,'Dr. Eleanor Vance','eleanor.vance@oxford-ecology.ac.uk','+44 20 7946 0991','Wildlife Protection Initiative','Our research group would like to spend 5 nights observing the Gir floral corridors and learn more about Surya Foundation’s veterinary rescue initiatives. What guided dawn walks are available?',1,'2026-10-31 10:30:34'),(6,'Kavita Patel','kavita.patel@mumbaifinance.in','+91 98700 12345','Room Reservation Inquiry','Inquiring regarding the Presidential Suite plunge pool heating. We are traveling with elderly parents and wish to verify private elevator or ground floor accessibility.',0,'2026-11-01 07:30:34'),(7,'Marcus Aurelius Lindqvist','marcus@nordictravel.se','+46 8 123 4567','Ayurvedic Spa & Wellness Package','Does Ashvkatha offer 7-day customized Panchakarma detox packages including private yoga master sessions on the woodland pavilion?',1,'2026-10-29 10:30:34');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','UPI','Online Payment') NOT NULL DEFAULT 'Online Payment',
  `transaction_id` varchar(100) NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'completed',
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_payment_txn` (`transaction_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (4,4,2,168.00,'UPI','PAY-20261101-6171B8','completed','2026-11-01 14:47:28'),(5,5,2,168.00,'Cash','PAY-20261101-11427F','completed','2026-11-01 14:47:50'),(6,6,2,699.00,'Online Payment','TXN-2E1C7B70-20261101','completed','2026-10-29 18:30:00'),(7,7,2,834.20,'UPI','TXN-E5A0BE3C-20261101','refunded','2026-11-07 18:30:00'),(8,8,2,4307.00,'Online Payment','TXN-212A9CF1-20261101','completed','2026-10-12 18:30:00'),(9,9,6,4563.00,'UPI','TXN-87584027-20261101','completed','2026-10-21 18:30:00'),(10,10,6,1195.20,'Cash','TXN-3F93339A-20261101','completed','2026-09-26 18:30:00'),(11,11,2,172.65,'Online Payment','PAY-20261101-848F53','completed','2026-11-01 15:13:08'),(12,12,2,172.65,'UPI','PAY-20261101-559EBD','refunded','2026-11-01 15:13:21'),(13,13,2,253.22,'Online Payment','PAY-20261101-3078FA','completed','2026-11-01 15:14:09'),(17,17,2,960.25,'Online Payment','PAY-20261101-57DF12','completed','2026-11-01 16:53:26');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `room_type_id` (`room_type_id`),
  KEY `idx_review_approved` (`is_approved`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,NULL,2,6,5,'The Presidential Suite was an ethereal experience. The plunge pool perched above the mist-covered trees made us feel at one with nature. Truly unmatched luxury and hospitality!',1,'2026-11-01 12:45:54'),(2,NULL,2,5,5,'Waking up to birdsong and relaxing in the outdoor Jacuzzi was pure heaven. Ashvkatha is by far the finest nature resort we have ever experienced.',1,'2026-11-01 12:45:54'),(3,NULL,2,2,5,'Every detail from the organic cedar furniture to the farm-fresh dining was exceptional. A magnificent sanctuary of peace.',1,'2026-11-01 12:45:54'),(6,NULL,2,1,5,'The stillness here is absolute. Waking up to the morning mist over the Gir canopy from our private veranda was an unforgettable restoration.',1,'2026-10-22 10:30:34'),(7,NULL,6,5,5,'Architectural perfection with river stone and reclaimed teak. The heated plunge pool overlooking the forest and the farm-to-table dining exceeded every luxury standard.',1,'2026-10-28 10:30:34'),(8,NULL,2,6,5,'The finest sanctuary resort in India. Knowing that our stay directly supports the Surya Foundation’s wildlife rescue hospital gave immense meaning to our stay.',1,'2026-10-30 10:30:34');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_types`
--

DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `short_desc` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 2,
  `size_sqft` int(11) NOT NULL DEFAULT 400,
  `bed_type` varchar(80) NOT NULL DEFAULT 'King Bed',
  `amenities` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_room_type_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_types`
--

LOCK TABLES `room_types` WRITE;
/*!40000 ALTER TABLE `room_types` DISABLE KEYS */;
INSERT INTO `room_types` VALUES (1,'Standard Room','standard-room','Cozy nature-view room crafted with sustainable teak accents.','Warm timber finishes and large glass panels immerse you in the tranquil whispers of the forest canopy. An ideal sanctuary for solo wanderers or couples seeking pure relaxation amidst nature.',150.00,2,380,'King Bed','Forest View, High-Speed Wi-Fi, Rain Shower, Organic Botanicals, Smart Climate Control, Artisanal Tea Bar','images/rooms/standard_room.jpg','active','2026-11-01 12:45:54'),(2,'Deluxe Room','deluxe-room','Expansive terrace room overlooking the verdant woodland slopes.','Spacious private retreat opening onto a stone terrace with panoramic woodland vistas. Features a plush lounge corner, handcrafted furnishings, and deep soaking tub surrounded by natural slate.',220.00,2,480,'King Bed','Private Stone Terrace, Deep Soaking Tub, Rain Shower, Mountain View, Nespresso Station, 55-inch OLED Display','images/rooms/deluxe_room.jpg','active','2026-11-01 12:45:54'),(3,'Premium Deluxe','premium-deluxe','Sophisticated open-concept suite bathed in golden natural light.','Elevated comfort perched among the gentle tree canopies. Designed with open-plan architecture, double dressing vanity, ambient fireplace, and private sunbed deck.',320.00,3,620,'California King','Canopy Sun Deck, Ambient Fireplace, Twin Rain Showers, Bathrobe & Slippers, Butler Service on Call, Panoramic Balcony','images/rooms/premium_deluxe.jpg','active','2026-11-01 12:45:54'),(4,'Executive Room','executive-room','Executive forest haven with private study and cedar wood deck.','Refined living with a dedicated executive study, seamless indoor-outdoor cedar deck, and private wellness bar. Tailored for discerning travellers and creative thinkers seeking serenity.',450.00,3,750,'Super King','Private Cedar Deck, Executive Work Space, Wine Chiller, Bose Sound System, Personalized Pillow Menu, Garden Vista','images/rooms/executive_room.jpg','active','2026-11-01 12:45:54'),(5,'Luxury Suite','luxury-suite','Opulent two-room suite with outdoor heated hydrotherapy jacuzzi.','A lavish two-room sanctuary featuring a sunken living salon, outdoor heated Jacuzzi, panoramic botanical bath, and bespoke natural stone masonry offering complete seclusion.',650.00,4,1150,'Master King + Daybed','Outdoor Heated Jacuzzi, Sunken Living Lounge, Gourmet Pantry, Free Airport Eco-Transfer, VIP Concierge, Fireplace','images/rooms/luxury_suite.jpg','active','2026-11-01 12:45:54'),(6,'Presidential Suite','presidential-suite','Ultra-luxury penthouse with private infinity plunge pool and dedicated butler.','The pinnacle of Ashvkatha hospitality. Spanning 2,200 sq.ft of private luxury with a personal infinity plunge pool, wrap-around panoramic forest terrace, private dining pavilion, and 24/7 dedicated private butler.',1200.00,6,2200,'2 Master King Suites','Private Infinity Plunge Pool, Wrap-around Terrace, Private Chef & Butler, Dining Pavilion, Steam & Sauna, Helicopter Transfer Option','images/rooms/presidential_suite.jpg','active','2026-11-01 12:45:54');
/*!40000 ALTER TABLE `room_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_type_id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `floor` int(11) NOT NULL DEFAULT 1,
  `status` enum('available','maintenance','cleaning','inactive') NOT NULL DEFAULT 'available',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`),
  KEY `room_type_id` (`room_type_id`),
  KEY `idx_room_number` (`room_number`),
  KEY `idx_room_status` (`status`),
  CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,1,'101',1,'available','Ground floor with direct zen garden walkway','2026-11-01 12:45:54'),(2,1,'102',1,'available','Ground floor overlooking east bamboo grove','2026-11-01 12:45:54'),(3,1,'103',1,'available','Ground floor peaceful courtyard facing','2026-11-01 12:45:54'),(4,1,'104',1,'available','Ground floor near herbal tea pavilion','2026-11-01 12:45:54'),(5,2,'201',2,'available','Second floor with morning sunrise vista','2026-11-01 12:45:54'),(6,2,'202',2,'available','Second floor with stone terrace and daybed','2026-11-01 12:45:54'),(7,2,'203',2,'available','Second floor with quiet woodland view','2026-11-01 12:45:54'),(8,2,'204',2,'available','Second floor corner suite with extra terrace','2026-11-01 12:45:54'),(9,3,'301',3,'available','Third floor tree canopy level with fireplace','2026-11-01 12:45:54'),(10,3,'302',3,'available','Third floor overlooking natural stream','2026-11-01 12:45:54'),(11,3,'303',3,'available','Third floor panoramic valley view','2026-11-01 12:45:54'),(12,3,'304',3,'available','Third floor corner with extended sun deck','2026-11-01 12:45:54'),(13,4,'401',4,'available','Fourth floor executive study & cedar deck','2026-11-01 12:45:54'),(14,4,'402',4,'available','Fourth floor mountain ridge horizon view','2026-11-01 12:45:54'),(15,4,'403',4,'available','Fourth floor south forest expanse view','2026-11-01 12:45:54'),(16,4,'404',4,'available','Fourth floor executive corner sanctuary','2026-11-01 12:45:54'),(17,5,'501',5,'available','Fifth floor with heated hydrotherapy jacuzzi','2026-11-01 12:45:54'),(18,5,'502',5,'available','Fifth floor with dual living salon','2026-11-01 12:45:54'),(19,5,'503',5,'available','Fifth floor with botanical garden bath','2026-11-01 12:45:54'),(20,5,'504',5,'available','Fifth floor sunset jacuzzi terrace','2026-11-01 12:45:54'),(21,6,'601',6,'available','Penthouse East with private infinity pool','2026-11-01 12:45:54'),(22,6,'602',6,'available','Penthouse North with panoramic mountain rim','2026-11-01 12:45:54'),(23,6,'603',6,'available','Penthouse West with private sunset pavilion','2026-11-01 12:45:54'),(24,6,'604',6,'available','Penthouse Grand Sanctuary with 360 terrace','2026-11-01 12:45:54');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `icon` varchar(50) NOT NULL DEFAULT 'spa',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'Ayurvedic Forest Spa & Hydrotherapy','Deeply therapeutic herbal body massages, aromatherapy, and hydrotherapy sessions conducted by certified Ayurvedic practitioners.',120.00,'spa','active','2026-11-01 12:45:54'),(2,'Guided Nature & Birding Safari','Early morning immersive walking safari through ancient botanical trails led by master wildlife naturalists.',75.00,'compass','active','2026-11-01 12:45:54'),(3,'Farm-to-Table Candlelight Dining','Five-course organic dining experience under the starlit sky featuring harvests from local sustainable sanctuary farms.',95.00,'utensils','active','2026-11-01 12:45:54'),(4,'Private Luxury Eco-Transfer','Chauffeured zero-emission electric vehicle pick-up and drop-off to and from the regional airport.',60.00,'car','active','2026-11-01 12:45:54'),(5,'Stargazing & Campfire Storytelling','An enchanting nocturnal session beside the crackling fire with astronomical telescopes and warm artisanal cider.',45.00,'moon','active','2026-11-01 12:45:54');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'hotel_name','Ashvkatha - Admire the Nature','2026-11-01 12:50:40'),(2,'hotel_tagline','Luxury Nature Retreat & Wildlife Sanctuary','2026-11-01 15:58:38'),(3,'hotel_phone','+91 2009 0222 22','2026-11-01 12:45:54'),(4,'hotel_email','contact@ashvkatha.in','2026-11-01 12:45:54'),(5,'hotel_address','Ashvkatha Nature Sanctuary, Junagadh, Gujarat 362001, India','2026-11-01 12:45:54'),(6,'currency_symbol','$','2026-11-01 12:45:54'),(7,'tax_rate_percent','15','2026-11-01 15:41:21'),(8,'check_in_time','11:00','2026-11-01 15:00:19'),(9,'check_out_time','11:00','2026-11-01 12:45:54'),(10,'cancellation_policy','Free cancellation up to 48 hours prior to arrival date.','2026-11-01 12:45:54'),(11,'background_video','https://cms.vantara.in/uploads/JUNE_2026_HOMEPAGE_HORIZONTAL_e6ea29466f.mp4','2026-11-01 12:45:54'),(12,'ambient_audio','https://cms.vantara.in/uploads/meditation_music_without_nature_sound_256142_4fbaeb3540.mp3','2026-11-01 12:45:54'),(13,'hotel_emergency_phone','+91 98765 43210','2026-11-01 13:31:25'),(14,'hotel_support_email','concierge@ashvkatha.in','2026-11-01 13:31:25'),(15,'airport_distance','Keshod Airport (IXK) - 38 km | Rajkot Int. Airport (HSR) - 105 km','2026-11-01 13:31:25'),(16,'min_stay_nights','1','2026-11-01 13:31:25'),(17,'max_stay_nights','24','2026-11-01 15:00:19'),(18,'max_advance_days','60','2026-11-01 16:16:08'),(19,'checkin_guidelines','Valid government-issued photo ID required for all adult guests upon arrival. Quiet sanctuary hours observed from 22:00 to 07:00.','2026-11-01 13:31:25'),(20,'complimentary_breakfast','Yes (Farm-to-table organic breakfast buffet included with all suites)','2026-11-01 13:31:25'),(21,'wifi_network_name','Ashvkatha-Sanctuary-HighSpeed (Complimentary Starlink fiber)','2026-11-01 13:31:25'),(22,'eco_pledge_note','Ashvkatha is an eco-sensitive sanctuary powered by 100% solar energy with zero single-use plastics and rainwater harvesting.','2026-11-01 13:31:25'),(23,'pet_policy','Eco-sanctuary guidelines: strictly trained pets welcome in designated private villas with advance notice.','2026-11-01 13:31:25'),(24,'google_maps_embed_url','https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d118671.21323366874!2d70.4727142!3d21.5222204!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3958018c6a277dc9%3A0xb36ef2013867c29e!2sJunagadh%2C%20Gujarat!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin','2026-11-01 13:31:25'),(25,'social_instagram','https://instagram.com/ashvkatha','2026-11-01 13:31:25'),(26,'social_facebook','https://facebook.com/ashvkatha','2026-11-01 13:31:25'),(27,'social_youtube','https://youtube.com/@ashvkatha','2026-11-01 13:31:25'),(28,'social_tripadvisor','https://tripadvisor.com','2026-11-01 13:31:25');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_user_email` (`email`),
  KEY `idx_user_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Ashvkatha Hotel Admin','admin@ashvkatha.com','+91 2009 0222 22','$2y$10$VRgbtI5YaPs.ydaAVe1dCufe/Bbl4rfZNTB.WydHfznedvPxWhSF2','admin','active','2026-11-01 12:45:54'),(2,'Alexander Vance','guest@ashvkatha.com','+1 555 382 9102','$2y$10$eFzOqbTrVSuMWcBQ2TmlsuvYJtPRF83GODdCU4O.eQIR2GEZS/KVK','customer','active','2026-11-01 12:45:54'),(6,'Rohit Mehta','rohit.mehta@example.com','+91 98111 22334','$2y$10$LZ1nk0IGtfWbsMtST.5kzuUI5PEBViZmcb.hNII7k5kxF18HZ1rO.','customer','active','2026-11-01 14:58:14');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-01 23:49:41
