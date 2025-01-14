CREATE DATABASE IF NOT EXISTS hire_now;
USE hire_now;

-- Crear la tabla api_consumers
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
    deleted_at DATETIME DEFAULT NULL
);
