
CREATE DATABASE IF NOT EXISTS `lms_db`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `lms_db`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `lessons`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `auth_provider` ENUM('email','google') NOT NULL DEFAULT 'email',
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `role` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `verification_code` VARCHAR(6) DEFAULT NULL,
    `verification_expires` DATETIME DEFAULT NULL,
    `reset_code` VARCHAR(6) DEFAULT NULL,
    `reset_expires` DATETIME DEFAULT NULL,
    `google_id` VARCHAR(255) DEFAULT NULL,
    `avatar` VARCHAR(500) DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    UNIQUE KEY `uq_users_phone` (`phone`),
    UNIQUE KEY `uq_users_google_id` (`google_id`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_status` (`status`),
    KEY `idx_users_auth_provider` (`auth_provider`),
    KEY `idx_users_created_at` (`created_at`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `courses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT 'Unknown',
    `level` VARCHAR(50) NOT NULL DEFAULT 'Unknown',
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `access` VARCHAR(50) NOT NULL DEFAULT 'Lifetime',
    `description` TEXT NOT NULL,
    `image` VARCHAR(255) NOT NULL DEFAULT 'image.png',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_courses_category` (`category`),
    KEY `idx_courses_level` (`level`),
    KEY `idx_courses_status` (`status`),
    KEY `idx_courses_created_at` (`created_at`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `lessons` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id` INT UNSIGNED NOT NULL,
    `lesson_name` VARCHAR(255) NOT NULL,
    `lesson_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `video` VARCHAR(255) NOT NULL,
    `duration` INT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (`id`),
    KEY `idx_lessons_course_id` (`course_id`),
    KEY `idx_lessons_course_order` (`course_id`,`lesson_order`),

    CONSTRAINT `fk_lessons_course`
        FOREIGN KEY (`course_id`)
        REFERENCES `courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- حساب الأدمن الوحيد

INSERT INTO `users` (
    `id`,
    `name`,
    `email`,
    `auth_provider`,
    `password`,
    `phone`,
    `role`,
    `status`,
    `created_at`,
    `updated_at`,
    `email_verified`,
    `verification_code`,
    `verification_expires`,
    `reset_code`,
    `reset_expires`,
    `google_id`,
    `avatar`
) VALUES (
    1,
    'Mahmoud Hussein',
    'mahmoud@admin.com',
    'email',
    '$2y$10$OH3FNqAIkiTf8AHtNx8DEeRJ5OuKWGgGFpQjHQrtQchUC26B8vM62',
    '1012345678',
    1,
    1,
    '2026-08-10 17:17:30',
    '2026-09-12 10:21:25',
    1,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL
);


SET FOREIGN_KEY_CHECKS = 1;