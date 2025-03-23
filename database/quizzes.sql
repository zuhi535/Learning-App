CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
);