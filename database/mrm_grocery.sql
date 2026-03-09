-- MRM Grocery and Wholesale Database
-- Import this in phpMyAdmin or run: mysql -u root -p < mrm_grocery.sql

CREATE DATABASE IF NOT EXISTS mrm_grocery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mrm_grocery;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 0,
    category_id INT,
    image VARCHAR(255) DEFAULT 'default.jpg',
    is_featured TINYINT(1) DEFAULT 0,
    is_on_sale TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Cart Table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    shipping_name VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200),
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- =====================
-- DEFAULT DATA
-- =====================

-- Admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('MRM Admin', 'admin@mrm.com', '$2y$10$YV7SopII4ieus7/LgsvgvuK6ruUme1gV3X1LZavjnWLKHK2IjPwBa', 'admin');

-- Sample regular user (password: user123)
INSERT INTO users (name, email, password, phone, address, role) VALUES
('Kamal Perera', 'kamal@example.com', '$2y$10$UJ/NszcTquB61dT2EueYfewW2lt/f9Be2UM6i1Km.ZWqywAv/mzLi', '0771234567', '123 Main St, Colombo', 'user');

-- Categories
INSERT INTO categories (name) VALUES
('Vegetables'),
('Fruits'),
('Dairy & Eggs'),
('Grains & Rice'),
('Beverages'),
('Snacks'),
('Meat & Fish'),
('Household');

-- Sample Products
INSERT INTO products (name, description, price, sale_price, quantity, category_id, is_featured, is_on_sale) VALUES
('Fresh Tomatoes (1kg)', 'Farm fresh red tomatoes, perfect for cooking and salads. Locally sourced.', 150.00, 120.00, 50, 1, 1, 1),
('Brinjal (Batu) 500g', 'Fresh local brinjal, great for curries and stir fries.', 80.00, NULL, 30, 1, 0, 0),
('Carrot (1kg)', 'Fresh crunchy carrots, great for salads and curries.', 90.00, 75.00, 45, 1, 1, 1),
('Leeks (500g)', 'Fresh green leeks with a mild onion flavor.', 60.00, NULL, 40, 1, 0, 0),
('Pumpkin (1kg)', 'Sweet yellow pumpkin for curries and soups.', 70.00, 55.00, 35, 1, 0, 1),
('Banana Ambul (bunch)', 'Sweet local bananas, fresh from the farm.', 120.00, NULL, 60, 2, 1, 0),
('Papaya (1pc)', 'Ripe sweet papaya, fresh and juicy.', 200.00, 160.00, 20, 2, 0, 1),
('Mango Karthakolomban (1kg)', 'Premium juicy mangoes, seasonal fresh.', 350.00, NULL, 25, 2, 1, 0),
('Pineapple (1pc)', 'Ripe sweet pineapple, ready to eat.', 180.00, 150.00, 30, 2, 1, 1),
('Watermelon (1pc)', 'Large sweet watermelon, perfect for summer.', 450.00, NULL, 15, 2, 0, 0),
('Farm Eggs (10pcs)', 'Fresh farm eggs, nutritious and protein rich.', 280.00, 250.00, 100, 3, 1, 1),
('Milk Fresh (1L)', 'Fresh pasteurized full cream milk.', 220.00, NULL, 80, 3, 0, 0),
('Yogurt Plain (500ml)', 'Thick creamy plain yogurt, locally made.', 150.00, 130.00, 60, 3, 0, 1),
('Cheese Slice (200g)', 'Processed cheese slices for sandwiches.', 380.00, NULL, 40, 3, 1, 0),
('Basmati Rice (5kg)', 'Premium long grain basmati rice, aromatic.', 1200.00, 1050.00, 40, 4, 1, 1),
('White Rice Samba (5kg)', 'Sri Lankan white samba rice, locally grown.', 900.00, NULL, 60, 4, 0, 0),
('Red Rice (5kg)', 'Healthy red rice with high fiber content.', 950.00, 850.00, 50, 4, 0, 1),
('Dhal (Masoor) 1kg', 'Red masoor dhal for delicious curries.', 280.00, NULL, 70, 4, 0, 0),
('Coca Cola (1.5L)', 'Refreshing cola drink, chilled.', 180.00, 150.00, 100, 5, 0, 1),
('Orange Juice Elephant (1L)', 'Pure squeezed orange juice, no preservatives.', 320.00, NULL, 50, 5, 1, 0),
('Mineral Water (1.5L)', 'Pure mineral water, fresh spring source.', 80.00, 65.00, 200, 5, 0, 1),
('Milo (400g)', 'Chocolate malt beverage for energy.', 450.00, NULL, 60, 5, 1, 0);
