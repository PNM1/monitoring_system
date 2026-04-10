CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
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
    location_string VARCHAR(100),
    quantity INT DEFAULT 1
);

INSERT INTO users (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO products (name, color, size, category, price, location_department, location_row, location_shelf, location_string, quantity) VALUES
('Пуховик зимний', 'Черный', 'XL', 'Куртки', 8990, 1, 1, 1, '1;1;1', 33),
('Пуховик зимний', 'Черный', 'L', 'Куртки', 8990, 1, 1, 2, '1;1;2', 4),
('Пуховик зимний', 'Черный', 'M', 'Куртки', 8990, 1, 1, 3, '1;1;3', 5),
('Куртка джинсовая', 'Синий', 'L', 'Куртки', 3490, 1, 2, 1, '1;2;1', 6),
('Куртка джинсовая', 'Синий', 'M', 'Куртки', 3490, 1, 2, 2, '1;2;2', 7),
('Куртка кожаная', 'Коричневый', 'XL', 'Куртки', 12990, 1, 3, 1, '1;3;1', 4),
('Футболка поло', 'Белый', 'S', 'Футболки', 1290, 2, 1, 1, '2;1;1', 52),
('Футболка поло', 'Белый', 'M', 'Футболки', 1290, 2, 1, 2, '2;1;2', 4),
('Футболка поло', 'Белый', 'L', 'Футболки', 1290, 2, 1, 3, '2;1;3', 7),
('Футболка поло', 'Черный', 'M', 'Футболки', 1290, 2, 1, 4, '2;1;4', 5),
('Футболка принт', 'Серый', 'S', 'Футболки', 990, 2, 2, 1, '2;2;1', 7), 
('Футболка принт', 'Серый', 'M', 'Футболки', 990, 2, 2, 2, '2;2;2', 9),
('Джинсы скинни', 'Синий', '28', 'Джинсы', 2990, 3, 1, 1, '3;1;1', 23),
('Джинсы скинни', 'Синий', '30', 'Джинсы', 2990, 3, 1, 2, '3;1;2', 13),
('Джинсы скинни', 'Синий', '32', 'Джинсы', 2990, 3, 1, 3, '3;1;3', 54),
('Джинсы скинни', 'Черный', '30', 'Джинсы', 2990, 3, 1, 4, '3;1;4', 7),
('Брюки классические', 'Серый', '48', 'Брюки', 3990, 3, 2, 1, '3;2;1', 4),
('Брюки классические', 'Серый', '50', 'Брюки', 3990, 3, 2, 2, '3;2;2', 9);

CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_size ON products(size);
CREATE INDEX idx_products_location ON products(location_string);