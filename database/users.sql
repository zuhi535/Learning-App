-- users tábla létrehozása
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL, -- Felhasználónév
    email VARCHAR(255) UNIQUE NOT NULL, -- E-mail cím
    password VARCHAR(255) NOT NULL, -- Jelszó (SHA-256 hash-elve)
    birthdate DATE, -- Születési idő
    gender ENUM('male', 'female', 'other'), -- Neme
    gdpr_consent BOOLEAN DEFAULT FALSE, -- GDPR nyilatkozat
    profile_pic VARCHAR(255) DEFAULT 'default.png', -- Profilkép
    role ENUM('admin', 'user') DEFAULT 'user', -- Felhasználó szerep
    streak INT DEFAULT 0, -- Streak (pl. napi bejelentkezések)
    score INT DEFAULT 0 -- Pontszám
);

-- friend_code oszlop hozzáadása
ALTER TABLE users ADD COLUMN friend_code VARCHAR(50) UNIQUE;