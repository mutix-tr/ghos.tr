-- Kategoriler Tablosu
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Varsayılan Kategoriler
INSERT IGNORE INTO categories (name, slug) VALUES 
('Korku', 'korku'), ('Bilimkurgu', 'bilimkurgu'), ('Siberpunk', 'siberpunk'), ('Distopya', 'distopya');

-- Hikayeler tablosuna yeni sütunlar ekleme
ALTER TABLE stories ADD COLUMN category_id INT DEFAULT 1;
ALTER TABLE stories ADD COLUMN cover_image VARCHAR(255) DEFAULT NULL;
