CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(50),
    size VARCHAR(20),
    category VARCHAR(50),
    price DECIMAL(10, 2),
    location_department INT,
    location_row INT,
    location_shelf INT,
    location_string VARCHAR(100)
);

INSERT INTO users (username, password_hash, is_admin) VALUES ('admin', '$2y$10$Ztk6JKbO4j1XaNdnPRxYfuI51RSMT6jDbp7vopHk22Wdym1pDaq16', TRUE);

INSERT INTO products (name, color, size, category, price, location_department, location_row, location_shelf, location_string) VALUES
('Пуховик зимний', 'Черный', 'XL', 'Куртки', 8990, 1, 1, 1, '1;1;1'),
('Пуховик зимний', 'Черный', 'L', 'Куртки', 8990, 1, 1, 2, '1;1;2'),
('Пуховик зимний', 'Черный', 'M', 'Куртки', 8990, 1, 1, 3, '1;1;3'),
('Куртка джинсовая', 'Синий', 'L', 'Куртки', 3490, 1, 2, 1, '1;2;1'),
('Куртка джинсовая', 'Синий', 'M', 'Куртки', 3490, 1, 2, 2, '1;2;2'),
('Куртка кожаная', 'Коричневый', 'XL', 'Куртки', 12990, 1, 3, 1, '1;3;1'),
('Футболка поло', 'Белый', 'S', 'Футболки', 1290, 2, 1, 1, '2;1;1'),
('Футболка поло', 'Белый', 'M', 'Футболки', 1290, 2, 1, 2, '2;1;2'),
('Футболка поло', 'Белый', 'L', 'Футболки', 1290, 2, 1, 3, '2;1;3'),
('Футболка поло', 'Черный', 'M', 'Футболки', 1290, 2, 1, 4, '2;1;4'),
('Футболка принт', 'Серый', 'S', 'Футболки', 990, 2, 2, 1, '2;2;1'),
('Футболка принт', 'Серый', 'M', 'Футболки', 990, 2, 2, 2, '2;2;2'),
('Джинсы скинни', 'Синий', '28', 'Джинсы', 2990, 3, 1, 1, '3;1;1'),
('Джинсы скинни', 'Синий', '30', 'Джинсы', 2990, 3, 1, 2, '3;1;2'),
('Джинсы скинни', 'Синий', '32', 'Джинсы', 2990, 3, 1, 3, '3;1;3'),
('Джинсы скинни', 'Черный', '30', 'Джинсы', 2990, 3, 1, 4, '3;1;4'),
('Брюки классические', 'Серый', '48', 'Брюки', 3990, 3, 2, 1, '3;2;1'),
('Брюки классические', 'Серый', '50', 'Брюки', 3990, 3, 2, 2, '3;2;2');

CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_size ON products(size);
CREATE INDEX idx_products_location ON products(location_string);