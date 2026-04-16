-- database/schema.sql
-- Run this once on mi-linux to set up your database

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,   -- bcrypt hashed
    role        ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cars (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    brand       VARCHAR(100) NOT NULL,
    model       VARCHAR(100) NOT NULL,
    year        YEAR        NOT NULL,
    price       DECIMAL(10,2) NOT NULL,
    mileage     INT          NOT NULL,
    fuel_type   ENUM('Petrol','Diesel','Electric','Hybrid') DEFAULT 'Petrol',
    transmission ENUM('Manual','Automatic') DEFAULT 'Manual',
    color       VARCHAR(50),
    image       VARCHAR(255),
    description TEXT,
    seller_id   INT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS favourites (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT NOT NULL,
    car_id    INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fav (user_id, car_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id)  REFERENCES cars(id)  ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    car_id     INT NOT NULL,
    sender_id  INT NOT NULL,
    message    TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id)    REFERENCES cars(id)   ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id)  ON DELETE CASCADE
);

-- Seed: default admin account  (password: Admin1234!)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@carsales.com',
 '$2y$12$abcdefghijklmnopqrstuuVGZzQ5b0FzlHH4JxP4bFHkqr0K5uB4.',
 'admin')
ON DUPLICATE KEY UPDATE id=id;
