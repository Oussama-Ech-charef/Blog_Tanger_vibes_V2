-- Tangier Vibes - Database schema
CREATE DATABASE IF NOT EXISTS tangier_blog
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tangier_blog;

-- Users table: registered site users (authors and admins)
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

-- Categories table: post categories and topics
CREATE TABLE IF NOT EXISTS categories (
    id_category   INT AUTO_INCREMENT PRIMARY KEY,
    cat_name      VARCHAR(100) NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_categories_name (cat_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts table: blog entries created by users
-- Index notes:
--   - FULLTEXT search omitted: app uses LIKE-based search, not MATCH...AGAINST
--   - Single-column idx_posts_id_user omitted: covered by composite idx_posts_user_status
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
    INDEX idx_posts_user_status (id_user, status),

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

-- Comments table: reader comments on posts
CREATE TABLE IF NOT EXISTS comments (
    id_comment    INT AUTO_INCREMENT PRIMARY KEY,
    id_post       INT NOT NULL,
    id_user       INT DEFAULT NULL,
    author_name   VARCHAR(100) NOT NULL,
    comment_text  TEXT NOT NULL,
    status        ENUM('approved', 'rejected', 'pending') NOT NULL DEFAULT 'pending',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_comments_created_at (created_at),
    INDEX idx_comments_post_status (id_post, status),
    INDEX idx_comments_user (id_user),

    CONSTRAINT fk_comments_post
        FOREIGN KEY (id_post) REFERENCES posts(id_post)
        ON DELETE CASCADE,

    CONSTRAINT fk_comments_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact messages table: messages sent via the contact form
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

-- Login attempts table: database-backed rate limiting for failed logins
CREATE TABLE IF NOT EXISTS login_attempts (
    id_login_attempt  INT AUTO_INCREMENT PRIMARY KEY,
    email             VARCHAR(150) NOT NULL,
    failed_attempts   INT NOT NULL DEFAULT 0,
    locked_until      DATETIME DEFAULT NULL,
    last_attempt      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_login_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table: site-wide key-value configuration
-- Reserved for future use. No application code currently reads this table.
CREATE TABLE IF NOT EXISTS settings (
    id_setting    INT AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(100) NOT NULL,
    setting_value TEXT NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log table: system activity feed for the admin dashboard
CREATE TABLE IF NOT EXISTS activity_log (
    id_activity   INT AUTO_INCREMENT PRIMARY KEY,
    action_type   VARCHAR(50) NOT NULL,
    description   VARCHAR(500) NOT NULL,
    user_id       INT DEFAULT NULL,
    entity_type   VARCHAR(50) DEFAULT NULL,
    entity_id     INT DEFAULT NULL,
    is_read       TINYINT(1) NOT NULL DEFAULT 0,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_activity_created_at (created_at),
    INDEX idx_activity_action_type (action_type),
    INDEX idx_activity_is_read (is_read),

    CONSTRAINT fk_activity_user
        FOREIGN KEY (user_id) REFERENCES users(id_user)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User notifications table: per-user notification system
-- Reserved for future use. No application code currently reads or writes to this table.
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

-- Seed data: default categories
INSERT INTO categories (cat_name) VALUES
    ('Beaches'),
    ('Food & Restaurants'),
    ('Culture & History'),
    ('Nature & Parks'),
    ('Hotels & Riads'),
    ('Nightlife')
ON DUPLICATE KEY UPDATE cat_name = VALUES(cat_name);


