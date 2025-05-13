CREATE DATABASE IF NOT EXISTS scraping_db;
USE scraping_db;

CREATE TABLE IF NOT EXISTS crawler_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  video_id VARCHAR(255) NOT NULL,
  title TEXT,
  description TEXT,
  url VARCHAR(2048),
  site_name VARCHAR(255),
  thumbnail_url VARCHAR(2048),
  embedURL VARCHAR(2048),
  tags TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_video_id ON crawler_posts(video_id);
CREATE INDEX idx_site_name ON crawler_posts(site_name);
CREATE INDEX idx_created_at ON crawler_posts(created_at);

CREATE TABLE IF NOT EXISTS crawler_status (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_id VARCHAR(255) NOT NULL,
  status VARCHAR(50),
  attempt_count INT DEFAULT 1,
  error TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_task_id ON crawler_status(task_id);
CREATE INDEX idx_status ON crawler_status(status);

GRANT ALL PRIVILEGES ON scraping_db.* TO 'scraper'@'%';
FLUSH PRIVILEGES;
