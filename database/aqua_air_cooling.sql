-- Aqua Air Cooling Database Schema
-- Import this file in phpMyAdmin or MySQL to set up the database
-- (Database is already selected in phpMyAdmin)

-- Users table (customer accounts)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin table (administrator accounts)
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `service_type` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `company_type` varchar(100) NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `visit_date` date DEFAULT NULL,
  `visit_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin account
-- Username: admin | Password: admin123
INSERT INTO `admin` (`name`, `username`, `password`) VALUES
('Administrator', 'admin', '$2y$10$sl.FXJfytASrJs.FfjDK6ey7NjvWMtW5kpQZiy5CwC48ul7wNLCWK')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Terms & Conditions Table
CREATE TABLE IF NOT EXISTS `terms_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_number` int(11) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `icon` varchar(100) NOT NULL DEFAULT 'fas fa-file-contract',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Terms & Conditions Data
INSERT INTO `terms_conditions` (`id`, `section_number`, `title`, `content`, `icon`, `is_active`) VALUES
(1, 1, 'Service Booking', 'Customers must provide accurate personal information while booking AC services. Incorrect information may delay or cancel the service.', 'fas fa-calendar-check', 1),
(2, 2, 'Service Charges', 'Inspection charges may apply. The final service cost depends on the type of repair, spare parts used, and the condition of the AC unit.', 'fas fa-file-invoice-dollar', 1),
(3, 3, 'Warranty', 'Warranty is applicable only on eligible repairs and replaced spare parts. Physical damage, misuse, or unauthorized repairs are not covered.', 'fas fa-shield-alt', 1),
(4, 4, 'Customer Responsibilities', 'Customers must provide safe and easy access to the AC unit. Electricity and water supply should be available during the service.', 'fas fa-user-check', 1),
(5, 5, 'Cancellation Policy', 'Service bookings may be cancelled before the technician reaches the location. Cancellation after technician arrival may incur a visiting charge.', 'fas fa-ban', 1),
(6, 6, 'Payments', 'Payment must be completed immediately after the service. We accept Cash, UPI, Debit/Credit Cards, and Online Payments.', 'fas fa-credit-card', 1),
(7, 7, 'Liability', 'Aqua Air Cooling is not responsible for any pre-existing damage, electrical issues, or manufacturer defects in the AC unit.', 'fas fa-exclamation-triangle', 1),
(8, 8, 'Privacy', 'Customer information is kept confidential and is used only for booking, service updates, and customer support.', 'fas fa-user-shield', 1),
(9, 9, 'Changes to Terms', 'Aqua Air Cooling reserves the right to modify these Terms & Conditions at any time without prior notice.', 'fas fa-edit', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- Services Table
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `badge` varchar(255) NOT NULL DEFAULT 'PROFESSIONAL AC SERVICE',
  `title` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT 'img/installation.jpg',
  `icon` varchar(255) NOT NULL DEFAULT 'img/icon/icon-01-light.png',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_text` varchar(100) NOT NULL DEFAULT '',
  `short_desc` text NOT NULL,
  `description` text NOT NULL,
  `features` text DEFAULT NULL,
  `estimated_time` varchar(100) NOT NULL DEFAULT '60 Mins Arrival',
  `warranty` varchar(100) NOT NULL DEFAULT '30 Days Guarantee',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Services Data
INSERT INTO `services` (`id`, `slug`, `name`, `badge`, `title`, `image`, `icon`, `price`, `price_text`, `short_desc`, `description`, `features`, `estimated_time`, `warranty`, `display_order`, `is_active`) VALUES
(1, 'ac_installation', 'AC Installation', 'PROFESSIONAL AC SERVICE', 'Professional AC Installation Service', 'img/installation.jpg', 'img/icon/icon-01-light.png', 1200.00, 'Starting From ₹1200', 'Professional AC installation with proper fitting and testing.', 'Get professional AC installation with proper fitting, accurate positioning and complete testing. Our technicians ensure that your air conditioner is installed safely and performs efficiently.', 'Professional AC installation\nProper indoor and outdoor unit fitting\nCopper pipe connection\nElectrical connection checking\nCooling performance testing\nLeakage and safety inspection', '60-90 Mins Arrival', '90 Days Warranty', 1, 1),
(2, 'ac_pipe', 'Copper Pipe', 'AC COPPER PIPE SERVICE', 'Professional AC Copper Pipe Service', 'img/pipe.webp', 'img/icon/icon-02-light.png', 425.00, 'Starting From ₹425', 'Quality copper pipe installation for reliable AC performance.', 'High-quality copper pipe installation and replacement for reliable AC performance and proper refrigerant flow.', 'High quality copper pipe\nProfessional pipe fitting\nProper refrigerant flow\nLeakage checking\nAccurate pipe measurement\nProfessional installation', '30 Mins Arrival', 'Heavy Duty Material', 2, 1),
(3, 'ac_insulation', 'AC Insulation', 'AC INSULATION SERVICE', 'Professional AC Insulation Service', 'img/punch.jpeg', 'img/icon/icon-03-light.png', 60.00, 'Starting From ₹60', 'Professional AC insulation to improve cooling efficiency.', 'Professional AC pipe insulation service to protect pipes, reduce condensation and improve cooling efficiency.', 'Quality insulation material\nProper pipe covering\nCondensation protection\nHeat protection\nProfessional fitting\nLong-lasting insulation', '15 Mins Arrival', 'Standard Material', 3, 1),
(4, 'ac_tape', 'Packing Tape', 'AC PACKING TAPE SERVICE', 'Professional AC Packing Tape Service', 'img/tape.jpeg', 'img/icon/icon-04-light.png', 25.00, 'Starting From ₹25', 'Quality packing tape service for safe and proper AC installation.', 'Reliable packing tape service for secure AC pipe wrapping and professional finishing.', 'Strong packing tape\nProfessional wrapping\nSecure pipe covering\nClean finishing\nWeather protection\nDurable application', '10 Mins Arrival', 'High Density Tape', 4, 1),
(5, 'ac_cable', 'Havells 4 Core Cable (2.5 sq mm)', 'AC ELECTRICAL CABLE SERVICE', 'Havells 4 Core Cable Installation', 'img/cable.webp', 'img/icon/icon-05-light.png', 60.00, 'Starting From ₹60', 'Reliable electrical cable installation for AC systems.', 'Professional AC electrical cable installation using quality Havells 4 Core 2.5 sq mm cable.', 'Havells 4 Core Cable\n2.5 sq mm cable\nProfessional electrical connection\nSafety inspection\nProper cable routing\nConnection testing', '15 Mins Arrival', 'Genuine Havells Wire', 5, 1),
(6, 'ac_repair', 'AC Repair', 'PROFESSIONAL AC REPAIR', 'Professional AC Repair Service', 'img/repair.jpg', 'img/icon/icon-06-light.png', 900.00, 'Starting From ₹900', 'Professional AC repair and fault diagnosis service.', 'Professional AC repair service for cooling problems, unusual sounds, electrical issues and other AC faults.', 'Complete AC inspection\nCooling problem diagnosis\nElectrical checking\nComponent inspection\nFault identification\nProfessional repair', '45-60 Mins Arrival', '30 Days Repair Warranty', 6, 1),
(7, 'ac_gas', 'AC Gas Refilling', 'AC GAS SERVICE', 'Professional AC Gas Refilling Service', 'img/gas.jpeg', 'img/icon/icon-01-light.png', 3250.00, 'Starting From ₹3250', 'AC gas checking and refilling for better cooling.', 'Professional AC gas checking and refilling service to restore proper cooling performance.', 'Gas pressure checking\nLeakage inspection\nProfessional gas refilling\nCooling performance test\nPipe connection checking\nComplete AC testing', '45 Mins Arrival', '60 Days Leak Guarantee', 7, 1),
(8, 'ac_maintenance', 'AC Maintenance', 'AC MAINTENANCE SERVICE', 'Professional AC Maintenance Service', 'img/maintenance.jpg', 'img/icon/icon-02-light.png', 600.00, 'Starting From ₹600', 'Regular AC maintenance to improve performance and life.', 'Complete AC maintenance service to keep your air conditioner efficient, clean and reliable.', 'Complete AC inspection\nCooling performance check\nElectrical inspection\nIndoor unit checking\nOutdoor unit checking\nPerformance testing', '45 Mins Arrival', '30 Days Service Guarantee', 8, 1),
(9, 'ac_cleaning', 'AC Cleaning', 'PROFESSIONAL AC CLEANING', 'Professional AC Cleaning Service', 'img/cleaning.jpg', 'img/icon/icon-03-light.png', 800.00, 'Starting From ₹800', 'Complete AC cleaning for cleaner and better cooling.', 'Professional AC cleaning service to remove dust, dirt and buildup and improve cooling performance.', 'Indoor unit cleaning\nFilter cleaning\nCoil cleaning\nDust removal\nDrainage checking\nCooling performance testing', '45-60 Mins Arrival', 'Deep Jet Foam Clean', 9, 1),
(10, 'ac_stand', 'AC Stand', 'AC STAND SERVICE', 'Professional AC Stand Installation', 'img/stand.jpeg', 'img/icon/icon-04-light.png', 1000.00, 'Starting From ₹1000', 'Strong and reliable AC stand installation.', 'Strong and reliable AC outdoor unit stand installation with proper alignment and safety checking.', 'Strong AC stand\nProper wall fitting\nAccurate alignment\nHeavy load support\nSafety checking\nProfessional installation', '30 Mins Arrival', 'Rust-Proof Metal Stand', 10, 1),
(11, 'ac_uninstallation', 'AC Uninstallation', 'AC UNINSTALLATION SERVICE', 'Professional AC Uninstallation Service', 'img/uninstallation.jpg', 'img/icon/icon-05-light.png', 1200.00, 'Starting From ₹1200', 'Safe and professional AC uninstallation service.', 'Safe and professional AC uninstallation service with gas pumping down and careful unit packing.', 'Safe AC uninstallation\nRefrigerant pump down\nElectrical disconnection\nPipe and bracket removal\nSafe handling of indoor and outdoor unit\nCleaning service area', '45 Mins Arrival', 'Safe Removal Guarantee', 11, 1),
(12, 'ac_amc', 'Annual Maintenance Contract (AMC)', 'ANNUAL MAINTENANCE CONTRACT', 'AC Annual Maintenance Contract (AMC)', 'img/amc.png', 'img/icon/icon-06-light.png', 5000.00, 'Starting From ₹5000', 'Complete annual AC maintenance for worry-free service.', 'Comprehensive AC Annual Maintenance Contract covering regular servicing, priority breakdown response and complete system maintenance throughout the year.', 'Multiple scheduled service visits\nPriority breakdown support\nDeep cleaning & chemical wash\nGas pressure checking & optimization\nElectrical and safety checkup\nDiscount on spare parts', '1 Year Contract', '1 Year Complete Protection', 12, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Contact Info / Settings Table
CREATE TABLE IF NOT EXISTS `contact_info` (
  `id` int(11) NOT NULL DEFAULT 1,
  `page_heading` varchar(255) NOT NULL DEFAULT 'Contact Us',
  `page_subheading` varchar(255) NOT NULL DEFAULT 'We are available for AC Installation, Repair & Maintenance Services.',
  `address` text NOT NULL,
  `phone` varchar(50) NOT NULL DEFAULT '+91 6354911971',
  `email` varchar(100) NOT NULL DEFAULT 'aquaaircooling@gmail.com',
  `working_hours` text NOT NULL,
  `services_text` text NOT NULL,
  `whatsapp_number` varchar(50) NOT NULL DEFAULT '916354911971',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Contact Info
INSERT INTO `contact_info` (`id`, `page_heading`, `page_subheading`, `address`, `phone`, `email`, `working_hours`, `services_text`, `whatsapp_number`) VALUES
(1, 'Contact Us', 'We are available for AC Installation, Repair & Maintenance Services.', '123 Main Street,\nRajkot, Gujarat', '+91 6354911971', 'aquaaircoolling@gmail.com', 'Monday - Saturday\n8:00 AM - 8:00 PM', 'AC Installation, Repair, Maintenance, Gas Filling & General AC Service.', '916354911971')
ON DUPLICATE KEY UPDATE `phone` = VALUES(`phone`);

-- AC Brands / Companies Table
CREATE TABLE IF NOT EXISTS `ac_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default AC Brands
INSERT INTO `ac_brands` (`id`, `name`, `is_active`, `display_order`) VALUES
(1, 'Voltas', 1, 1),
(2, 'Daikin', 1, 2),
(3, 'LG', 1, 3),
(4, 'Samsung', 1, 4),
(5, 'Blue Star', 1, 5),
(6, 'Hitachi', 1, 6),
(7, 'Carrier', 1, 7),
(8, 'Panasonic', 1, 8),
(9, 'Lloyd', 1, 9),
(10, 'Godrej', 1, 10),
(11, 'Haier', 1, 11),
(12, 'Mitsubishi Electric', 1, 12),
(13, 'O General', 1, 13),
(14, 'IFB', 1, 14),
(15, 'Whirlpool', 1, 15),
(16, 'Other', 1, 16)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- AC Original Spare Parts Table
CREATE TABLE IF NOT EXISTS `ac_original_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default AC Original Spare Parts
INSERT INTO `ac_original_parts` (`id`, `name`, `price`, `is_active`, `display_order`) VALUES
(1, 'COMPRESSOR PCB - 8250', 8250.00, 1, 1),
(2, 'OUTDOOR FAN MOTOR - 1900', 1900.00, 1, 2),
(3, 'INDOOR FAN MOTOR - 2680', 2680.00, 1, 3),
(4, 'COMPRESSOR - 10000', 10000.00, 1, 4),
(5, 'FAN BLED - 850', 850.00, 1, 5),
(6, 'CSR POWER WIRE - 450', 450.00, 1, 6),
(7, 'INDOOR PCB - 4500', 4500.00, 1, 7),
(8, 'V - SPRING - 900', 900.00, 1, 8),
(9, 'H - SPRING - 900', 900.00, 1, 9),
(10, 'BASE - 4600', 4600.00, 1, 10),
(11, 'DRAIN PIPE - 220', 220.00, 1, 11),
(12, 'BLOWER - 850', 850.00, 1, 12),
(13, 'COOLING COIL - 3800', 3800.00, 1, 13),
(14, 'ROOM SENSOR - 950', 950.00, 1, 14),
(15, 'None (Service Only)', 0.00, 1, 15)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Booking Form Settings Table
CREATE TABLE IF NOT EXISTS `booking_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `hero_badge` varchar(255) NOT NULL DEFAULT '⭐ Trusted AC Service Since 2024',
  `hero_title` varchar(255) NOT NULL DEFAULT 'Book Your AC Service In Just 2 Minutes',
  `hero_text` text NOT NULL,
  `promo_title` varchar(255) NOT NULL DEFAULT 'Professional AC Service At Your Doorstep',
  `rating_text` varchar(100) NOT NULL DEFAULT '4.9/5 (1,450+ Happy Clients)',
  `ticker_text` varchar(255) NOT NULL DEFAULT '14 bookings completed today in your area!',
  `arrival_guarantee_gu` text NOT NULL,
  `arrival_guarantee_en` text NOT NULL,
  `max_coupon_discount` int(11) NOT NULL DEFAULT 10,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Booking Settings
INSERT INTO `booking_settings` (`id`, `hero_badge`, `hero_title`, `hero_text`, `promo_title`, `rating_text`, `ticker_text`, `arrival_guarantee_gu`, `arrival_guarantee_en`, `max_coupon_discount`) VALUES
(1, '⭐ Trusted AC Service Since 2024', 'Book Your AC Service In Just 2 Minutes', 'Fast • Reliable • Affordable\n\nBook AC Installation, Repair, Cleaning, Gas Refilling, AMC and Maintenance Service Online.', 'Professional AC Service At Your Doorstep', '4.9/5 (1,450+ Happy Clients)', '14 bookings completed today in your area!', 'જો તમારી બુકિંગ Approved થશે ત્યારબાદ 60 મિનિટમાં ટેકનિશિયન તમારા ઘરે આવશે.', '(Once your booking is approved by Admin, our expert technician will arrive at your home within 60 minutes.)', 10)
ON DUPLICATE KEY UPDATE `hero_title` = VALUES(`hero_title`);




