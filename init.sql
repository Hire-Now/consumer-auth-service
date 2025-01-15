-- DROP DATABASE IF EXISTS hire_now;
CREATE DATABASE IF NOT EXISTS hire_now;
USE hire_now;

CREATE TABLE IF NOT EXISTS api_consumers (
    id CHAR(36) PRIMARY KEY, 
    name VARCHAR(255) NOT NULL,
    client_id CHAR(36) UNIQUE NOT NULL, 
    client_secret VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    INDEX `idx_name` (`name`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_last_activity` (`last_activity`),
    INDEX `idx_created_at` (`created_at`)
);

DROP TABLE IF EXISTS jwt_tokens;

CREATE TABLE IF NOT EXISTS `jwt_tokens` (
    `id` CHAR(36) NOT NULL,
    `entity_id` CHAR(36) NOT NULL,
    `jti` VARCHAR(255) NOT NULL,
    `status` ENUM('active', 'revoked', 'expired') NOT NULL DEFAULT 'active',
    `expiry_time` VARCHAR(30) NOT NULL,
    `type_time` VARCHAR(30) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `owner` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_entity_id` (`entity_id`),
    INDEX `idx_jti` (`jti`),
    INDEX `idx_status` (`status`)
);
