-- --------------------------------------------------------
-- JASS Logistics Production Schema 
-- Engine: InnoDB (Supports Transactions & Foreign Keys)
-- Charset: utf8mb4 (Full Unicode support including Emojis)
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Table Structure: admin_users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default admin user ONLY if table is empty
INSERT INTO `admin_users` (`username`, `password`)
SELECT 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' -- 'password' hashed
WHERE NOT EXISTS (SELECT 1 FROM `admin_users` LIMIT 1);

-- --------------------------------------------------------
-- Table Structure: site_settings
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default settings ONLY if table is empty
INSERT INTO `site_settings` (`setting_key`, `setting_value`)
SELECT * FROM (
  SELECT 'about_whoWeAre' AS setting_key, 'We are a leading logistics solutions provider based in the US, dedicated to seamlessly connecting businesses to global markets.' AS setting_value UNION ALL
  SELECT 'about_mission', 'At JASS Logistics, our mission is to seamlessly connect businesses to global markets through innovative, reliable, and efficient logistics solutions.' UNION ALL
  SELECT 'about_vision', 'At JASS Logistics, our vision is to be the logistics partner of choice for businesses worldwide.' UNION ALL
  
  SELECT 'stats_countries', '150+' UNION ALL
  SELECT 'stats_clients', '12K+' UNION ALL
  SELECT 'stats_delivery', '99.2%' UNION ALL
  SELECT 'stats_support', '24/7' UNION ALL
  
  SELECT 'contact_address', '3500 Logistics Center Drive, Jacksonville FL, 32218, United States.' UNION ALL
  SELECT 'contact_phone1', '+1 (800) 123-4567' UNION ALL
  SELECT 'contact_phone2', '+1 (800) 987-6543' UNION ALL
  SELECT 'contact_email1', 'info@jasslogistics.com' UNION ALL
  SELECT 'contact_email2', 'support@jasslogistics.com' UNION ALL
  SELECT 'social_facebook', '#' UNION ALL
  SELECT 'social_twitter', '#' UNION ALL
  SELECT 'social_linkedin', '#' UNION ALL
  SELECT 'social_instagram', '#' UNION ALL
  
  SELECT 'website_logo', 'logo.png' UNION ALL
  SELECT 'website_favicon', 'uploads/1771651544932-484533847.jpg' UNION ALL
  SELECT 'website_mapEmbed', '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12916.974271466519!2d76.10797591413386!3d10.410129228758409!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ba7f342cf886e37%3A0x8b237cd2db24c3bc!2sTriprayar%2C%20Kerala!5e1!3m2!1sen!2sin!4v1771650930993!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `site_settings` LIMIT 1);

-- --------------------------------------------------------
-- Table Structure: services
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` varchar(100),
  `title` varchar(255) NOT NULL,
  `description` text,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_services_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed services ONLY if table is empty
INSERT INTO `services` (`icon`, `title`, `description`, `display_order`)
SELECT * FROM (
  SELECT 'fa-truck' AS icon, 'Ground Transport' AS title, 'Seamless door-to-door delivery via our extensive network of trucks and rail partners across continents.' AS description, 1 AS display_order UNION ALL
  SELECT 'fa-warehouse', 'Warehousing', 'Secure storage facilities equipped with modern inventory management systems to keep your goods safe.', 2 UNION ALL
  SELECT 'fa-industry', 'Industrial pallets', 'Our Industrial Pallet Services are designed to support seamless logistics, warehousing, and supply chain operations', 3
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `services` LIMIT 1);

-- --------------------------------------------------------
-- Table Structure: features
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` varchar(100),
  `title` varchar(255) NOT NULL,
  `description` text,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_features_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed features ONLY if table is empty
INSERT INTO `features` (`icon`, `title`, `description`, `display_order`)
SELECT * FROM (
  SELECT 'fa-shield-halved' AS icon, 'Fully Insured' AS title, 'Comprehensive insurance coverage for all shipments, ensuring your cargo is protected against unforeseen circumstances.' AS description, 1 AS display_order UNION ALL
  SELECT 'fa-satellite-dish', 'Real-Time Tracking', 'Advanced GPS and IoT integration allowing you to monitor your shipments 24/7 with live updates and notifications.', 2 UNION ALL
  SELECT 'fa-globe', 'Global Network', 'Partnerships with leading carriers and agents in 150+ countries to ensure seamless door-to-door delivery worldwide.', 3
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `features` LIMIT 1);

-- --------------------------------------------------------
-- Table Structure: hero_slides
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subtitle` varchar(255),
  `title_line1` varchar(255),
  `title_highlight` varchar(255),
  `description` text,
  `background_image` varchar(500),
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hero_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed hero slides ONLY if table is empty
INSERT INTO `hero_slides` (`subtitle`, `title_line1`, `title_highlight`, `description`, `background_image`, `display_order`)
SELECT * FROM (
  SELECT 'Ava Logistics and Trading FZE' AS subtitle, 'Industrial packaging and project logistics solutions' AS title_line1, 'Global Logistics Solutions' AS title_highlight, 'Reliable, cost-effective, and advanced logistics solutions for businesses worldwide. We connect your cargo to the world through air, ocean, and land transportation.' AS description, 'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80' AS background_image, 1 AS display_order UNION ALL
  SELECT 'Global Logistics Solutions', 'Your Trusted Partner for', 'Global Logistics Solutions', 'Reliable, cost-effective, and advanced logistics solutions for businesses worldwide. We connect your cargo to the world through air, ocean, and land transportation.', 'uploads/1771652921170-320639652.jpg', 2
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `hero_slides` LIMIT 1);
