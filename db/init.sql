-- ============================================================
-- Contact Form — Database Bootstrap
-- Auto-runs on first MySQL container boot via /docker-entrypoint-initdb.d/
-- Also runnable standalone against any MySQL 8 instance.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `contact_form`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `contact_form`;

-- ------------------------------------------------------------
-- Table: entries
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `entries`;

CREATE TABLE `entries` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100)  NOT NULL,
    `email`      VARCHAR(255)  NOT NULL,
    `phone`      VARCHAR(20)   NOT NULL COMMENT 'Normalized E.164 format, e.g. +27821234567',
    `message`    TEXT          NOT NULL,
    `ip_address` VARBINARY(16) NULL     COMMENT 'Packed binary via inet_pton(); supports IPv4 + IPv6',
    `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_email`      (`email`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Dummy data (10 rows, phone numbers in E.164 as the app stores them)
-- ------------------------------------------------------------
INSERT INTO `entries` (`name`, `email`, `phone`, `message`, `created_at`) VALUES
('Thabo Mokoena',
 'thabo.mokoena@example.co.za',
 '+27821234567',
 'Hi, I would like a quote for website development. We need something modern with a booking system for our guesthouse in Dullstroom.',
 NOW() - INTERVAL 9 DAY),

('Sarah van Wyk',
 'sarah.vanwyk@example.com',
 '+27719876543',
 'Please call me back regarding the marketing package you advertised. I am available weekdays between 9am and 4pm.',
 NOW() - INTERVAL 7 DAY),

('Sipho Dlamini',
 'sipho.d@example.co.za',
 '+27831234567',
 'Interested in your consulting services for a small retail business. Do you offer an initial free consultation?',
 NOW() - INTERVAL 5 DAY),

('Anele Nkosi',
 'anele.nkosi@example.com',
 '+27605551234',
 'Can you please send me more information about pricing and timelines? We are planning to launch in Q3.',
 NOW() - INTERVAL 3 DAY),

('Pieter Botha',
 'pieter.botha@example.co.za',
 '+27837654321',
 'Excellent service, thank you for the quick turnaround on our last project. We will definitely be in touch again soon!',
 NOW() - INTERVAL 2 DAY),

('Naledi Khumalo',
 'naledi.k@example.co.za',
 '+27723334444',
 'I saw your ad on Facebook and would like to know if you build e-commerce sites. I sell handmade jewellery online.',
 NOW() - INTERVAL 1 DAY),

('Johan Pretorius',
 'johan.pretorius@example.com',
 '+27123456789',
 'Do you offer services in Pretoria? We have an office in Centurion and are looking for a long-term development partner.',
 NOW() - INTERVAL 6 HOUR),

('Zanele Mthembu',
 'zanele.mthembu@example.co.za',
 '+27845556666',
 'Following up on my previous enquiry — just checking if you received my details and if there is any update on a quote.',
 NOW() - INTERVAL 2 HOUR),

('Riaan du Plessis',
 'riaan.dup@example.com',
 '+27737778888',
 'Hello, I would like to discuss a possible integration with our existing CRM system. Please let me know your availability for a call.',
 NOW() - INTERVAL 45 MINUTE),

('Lerato Sithole',
 'lerato.sithole@example.co.za',
 '+27828889999',
 'I am looking for a developer to help with a mobile-first web application for our NPO. We have a limited budget but a great cause!',
 NOW() - INTERVAL 15 MINUTE);