-- Property listings (manual admin entries and future API sync)
CREATE TABLE IF NOT EXISTS properties (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    source ENUM('manual', 'api') NOT NULL DEFAULT 'manual',
    county VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    town VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    displayable_address VARCHAR(255) NOT NULL,
    image_path VARCHAR(500) NULL DEFAULT NULL,
    thumbnail_path VARCHAR(500) NULL DEFAULT NULL,
    latitude DECIMAL(10, 7) NULL DEFAULT NULL,
    longitude DECIMAL(10, 7) NULL DEFAULT NULL,
    bedrooms TINYINT UNSIGNED NOT NULL,
    bathrooms TINYINT UNSIGNED NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    property_type ENUM('house', 'flat', 'apartment', 'bungalow', 'land', 'commercial') NOT NULL,
    listing_type ENUM('sale', 'rent') NOT NULL,
    created_by BIGINT UNSIGNED NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_properties_source_deleted (source, deleted_at),
    KEY idx_properties_created_by (created_by),
    CONSTRAINT fk_properties_created_by FOREIGN KEY (created_by) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
