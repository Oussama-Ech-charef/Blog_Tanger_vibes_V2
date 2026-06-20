-- ============================================================
-- Tangier Vibes — Final Production Database Schema
-- Merged from: script.sql + migration_001_dashboard.sql
-- Optimized with proper indexes, foreign keys, constraints
-- ============================================================

CREATE DATABASE IF NOT EXISTS tangier_blog
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tangier_blog;

-- ============================================================
-- users: Registered site users (authors + admins)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id_user       INT AUTO_INCREMENT PRIMARY KEY,
    user_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    avatar        VARCHAR(255) DEFAULT NULL,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- categories: Post categories / topics
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id_category   INT AUTO_INCREMENT PRIMARY KEY,
    cat_name      VARCHAR(100) NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_categories_name (cat_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- posts: Blog / place entries created by users
-- ============================================================
CREATE TABLE IF NOT EXISTS posts (
    id_post           INT AUTO_INCREMENT PRIMARY KEY,
    id_category       INT NOT NULL,
    id_user           INT DEFAULT NULL,
    id_approved_by    INT DEFAULT NULL,
    title             VARCHAR(255) NOT NULL,
    image             VARCHAR(255) DEFAULT NULL,
    content           TEXT NOT NULL,
    status            ENUM('draft', 'pending', 'published', 'rejected') NOT NULL DEFAULT 'pending',
    rejection_reason  TEXT DEFAULT NULL,
    approved_at       TIMESTAMP NULL DEFAULT NULL,
    reviewed_at       DATETIME DEFAULT NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_posts_status (status),
    INDEX idx_posts_created_at (created_at),

    CONSTRAINT fk_posts_category
        FOREIGN KEY (id_category) REFERENCES categories(id_category)
        ON DELETE CASCADE,

    CONSTRAINT fk_posts_author
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE SET NULL,

    CONSTRAINT fk_posts_approver
        FOREIGN KEY (id_approved_by) REFERENCES users(id_user)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- comments: Reader comments on posts
-- ============================================================
CREATE TABLE IF NOT EXISTS comments (
    id_comment    INT AUTO_INCREMENT PRIMARY KEY,
    id_post       INT NOT NULL,
    author_name   VARCHAR(100) NOT NULL,
    comment_text  TEXT NOT NULL,
    status        ENUM('approved', 'rejected', 'pending') NOT NULL DEFAULT 'approved',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_comments_created_at (created_at),

    CONSTRAINT fk_comments_post
        FOREIGN KEY (id_post) REFERENCES posts(id_post)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- contact_messages: Messages sent via the contact form
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id_message    INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(150) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    subject       VARCHAR(255) NOT NULL,
    message       TEXT NOT NULL,
    is_read       TINYINT(1) NOT NULL DEFAULT 0,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_contact_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- login_attempts: DB-based rate limiting for failed logins
-- ============================================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id_login_attempt  INT AUTO_INCREMENT PRIMARY KEY,
    email             VARCHAR(150) NOT NULL,
    failed_attempts   INT NOT NULL DEFAULT 0,
    locked_until      DATETIME DEFAULT NULL,
    last_attempt      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_login_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- settings: Site-wide key-value configuration
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id_setting    INT AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(100) NOT NULL,
    setting_value TEXT NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- activity_log: System activity feed for the admin dashboard
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id_activity   INT AUTO_INCREMENT PRIMARY KEY,
    action_type   VARCHAR(50) NOT NULL,
    description   VARCHAR(500) NOT NULL,
    user_id       INT DEFAULT NULL,
    entity_type   VARCHAR(50) DEFAULT NULL,
    entity_id     INT DEFAULT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_activity_created_at (created_at),
    INDEX idx_activity_action_type (action_type),

    CONSTRAINT fk_activity_user
        FOREIGN KEY (user_id) REFERENCES users(id_user)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Seed Data: Default categories
-- ============================================================
INSERT INTO categories (cat_name) VALUES
    ('Beaches'),
    ('Food & Restaurants'),
    ('Culture & History'),
    ('Nature & Parks'),
    ('Hotels & Riads'),
    ('Nightlife')
ON DUPLICATE KEY UPDATE cat_name = VALUES(cat_name);


-- ============================================================
-- Seed Data: Default settings
-- ============================================================
INSERT INTO settings (setting_key, setting_value) VALUES
    ('site_name', 'Tangier Vibes'),
    ('site_description', 'Discover the best of Tangier, Morocco. Beaches, restaurants, culture, hotels, and hidden gems curated by locals.'),
    ('admin_email', 'admin@tangiervibes.com'),
    ('posts_per_page', '6'),
    ('theme_color', '#0047AB')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);


-- ============================================================
-- user_notifications: Per-user notification system
-- ============================================================
CREATE TABLE IF NOT EXISTS user_notifications (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    id_user         INT NOT NULL,
    type            VARCHAR(50) NOT NULL COMMENT 'post_approved, post_rejected, new_comment, system',
    message         VARCHAR(500) NOT NULL,
    link            VARCHAR(255) DEFAULT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_notif_user (id_user),
    INDEX idx_user_notif_read (id_user, is_read),
    INDEX idx_user_notif_created (created_at),

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Migration: Add is_read to activity_log for admin notifications
-- ============================================================
ALTER TABLE activity_log
    ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER entity_id,
    ADD INDEX idx_activity_is_read (is_read);

-- ============================================================
-- Performance indexes for common query patterns
-- ============================================================
CREATE INDEX idx_posts_id_user ON posts(id_user);
CREATE INDEX idx_posts_user_status ON posts(id_user, status);
CREATE INDEX idx_comments_post_status ON comments(id_post, status);
CREATE FULLTEXT INDEX idx_posts_search ON posts(title, content);


