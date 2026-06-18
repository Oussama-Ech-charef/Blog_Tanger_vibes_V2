<div align="center">

# 🧭 Tangier Vibes

**A Tourism Blog & City Guide Platform for Tangier, Morocco**

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

</div>

---

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [User Features](#user-features)
- [Admin Features](#admin-features)
- [Authentication & Security](#authentication--security)
- [Technologies Used](#technologies-used)
- [Database Overview](#database-overview)
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Configuration](#configuration)
- [Screenshots](#screenshots)
- [Future Enhancements](#future-enhancements)
- [License](#license)
- [Author](#author)

---

## Overview

Tangier Vibes is a content-driven web platform for discovering and sharing places, culture, restaurants, beaches, hotels, and attractions in Tangier, Morocco. It features a full content management workflow with user-submitted posts, admin moderation, multi-language support (English, French, Arabic), and responsive design across desktop and mobile devices.

Built with vanilla PHP and MySQL, the platform provides role-based access control, CSRF-protected forms, database-backed rate limiting, and a complete publish–review–reject lifecycle for content.

---

## Key Features

| Feature | Description |
|---|---|
| **User Registration & Login** | Account creation with name, email, and hashed password; session-based authentication |
| **Role Management** | Two roles (`user` / `admin`) with distinct permissions enforced server-side |
| **Post Creation** | Form with title, category selection, rich content body, and image upload with MIME validation |
| **Post Editing** | Pre-filled form allowing authors to modify their own posts |
| **Post Deletion** | Owners can delete their posts; admins can delete any post |
| **Publication Workflow** | Four-status lifecycle: `draft` → `pending` → `published` / `rejected` |
| **Admin Moderation** | Approve pending posts or reject with a required textual reason |
| **Rejection Feedback** | Users view rejection reasons on the dashboard |
| **Dashboard Analytics** | Stats cards (total, published, pending, draft, rejected) and full post management table |
| **Category System** | Six predefined categories with filterable explore page |
| **Search** | Keyword search across post titles and category names |
| **Pagination** | Page-based navigation on the explore page (6 posts per page) |
| **Comments System** | Public comment submission with CSRF protection, auto-scroll to comments, and success popup |
| **Contact Form** | Name, email, subject, message with CSRF validation and database storage |
| **Multi-Language Support** | English, French, and Arabic with RTL layout support for Arabic |
| **Image Upload** | MIME-type validation, extension whitelist, secure randomized filenames |
| **Responsive Design** | Mobile hamburger menu, adaptive grid layout across all screen sizes |
| **Google Maps** | Embedded map centered on Tangier on post detail pages |
| **FAQ Section** | Accordion-style frequently asked questions on the contact page |

---

## User Features

- Browse published posts on the home and explore pages
- Filter posts by category
- Search posts by keyword
- Register an account and log in
- Create new posts (submitted as `pending` for admin review)
- Edit and delete own posts
- View own post status and rejection reasons on the dashboard
- Leave comments on published posts
- Switch between English, French, and Arabic languages
- Submit contact messages

---

## Admin Features

- All user features
- Approve pending posts (publishes immediately)
- Reject posts with a required textual reason
- View all users' posts in a unified dashboard
- Delete any post regardless of ownership
- Publish posts directly without moderation
- View full analytics: total, published, pending, draft, and rejected post counts

---

## Authentication & Security

| Mechanism | Implementation |
|---|---|
| **Password Hashing** | `password_hash()` with `PASSWORD_DEFAULT` on registration; `password_verify()` on login |
| **CSRF Protection** | Per-session tokens generated via `bin2hex(random_bytes(32))`; validated with `hash_equals()` on all state-changing actions |
| **Prepared Statements** | All database queries use PDO prepared statements with named parameters — no raw SQL interpolation |
| **Rate Limiting** | Database-backed login attempt tracking; 5 failed attempts triggers 10-minute lockout; survives browser restarts |
| **Session Security** | `session_regenerate_id(true)` on login; 30-minute inactivity timeout; session cookie cleared on logout |
| **XSS Prevention** | All user output escaped with `htmlspecialchars()` across every view |
| **Input Validation** | Email format validation, password minimum length (6), required field checks, string length limits |
| **Role-Based Access** | Server-side checks on every admin-only action; post ownership verified before edit or delete |
| **Security Headers** | `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy` |
| **Upload Validation** | `getimagesize()` MIME check (JPEG, PNG, WebP); extension whitelist; file size enforcement |
| **POST Enforcement** | State-changing actions (delete, logout, approve, reject) restricted to POST requests |

---

## Technologies Used

| Technology | Purpose |
|---|---|
| **PHP** | Server-side scripting and business logic |
| **MySQL** | Relational database for data persistence |
| **PDO** | Secure database access with prepared statements |
| **HTML5** | Semantic page structure with Open Graph and Twitter Card meta tags |
| **CSS3** | Custom properties, Flexbox, CSS Grid, responsive media queries |
| **JavaScript** | Mobile menu, auth modal, AJAX login/register, image preview, FAQ accordion, comment popup |
| **Font Awesome** | Icon library (free tier) across the interface |
| **Google Maps Embed API** | Fixed embedded map centered on Tangier |
| **Google Fonts (Inter)** | Primary typography |

---

## Database Overview

The database `tangier_blog` contains six tables:

### `users` — Registered accounts

| Column | Type | Notes |
|---|---|---|
| `id_user` | INT (PK) | Auto-increment |
| `user_name` | VARCHAR(100) | Full name |
| `email` | VARCHAR(150) | Unique |
| `password` | VARCHAR(255) | Hashed |
| `role` | ENUM('user','admin') | Default `user` |
| `created_at` | TIMESTAMP | Auto-generated |

### `categories` — Post classification

| Column | Type | Notes |
|---|---|---|
| `id_category` | INT (PK) | Auto-increment |
| `cat_name` | VARCHAR(100) | Unique |
| `created_at` | TIMESTAMP | Auto-generated |

Default rows: Beaches, Food & Restaurants, Culture & History, Nature & Parks, Hotels & Riads, Nightlife.

### `posts` — Blog content

| Column | Type | Notes |
|---|---|---|
| `id_post` | INT (PK) | Auto-increment |
| `id_category` | INT (FK) | → `categories` (`ON DELETE CASCADE`) |
| `id_user` | INT (FK) | → `users` (`ON DELETE CASCADE`) |
| `id_approved_by` | INT (FK) | → `users`, nullable (`ON DELETE SET NULL`) |
| `title` | VARCHAR(255) | Post title |
| `image` | VARCHAR(255) | File path, nullable |
| `content` | TEXT | Post body |
| `status` | ENUM('draft','pending','published','rejected') | Publication state |
| `rejection_reason` | TEXT | Admin feedback, nullable |
| `approved_at` | TIMESTAMP | Nullable |
| `created_at` | TIMESTAMP | Auto-generated |
| `updated_at` | TIMESTAMP | Auto-updated |

### `comments` — Post comments

| Column | Type | Notes |
|---|---|---|
| `id_comment` | INT (PK) | Auto-increment |
| `id_post` | INT (FK) | → `posts` (`ON DELETE CASCADE`) |
| `author_name` | VARCHAR(100) | Display name |
| `comment_text` | TEXT | Comment body |
| `created_at` | TIMESTAMP | Auto-generated |

### `contact_messages` — Contact form submissions

| Column | Type | Notes |
|---|---|---|
| `id_message` | INT (PK) | Auto-increment |
| `full_name` | VARCHAR(150) | Sender name |
| `email` | VARCHAR(150) | Sender email |
| `subject` | VARCHAR(255) | Message subject |
| `message` | TEXT | Message body |
| `created_at` | TIMESTAMP | Auto-generated |

### `login_attempts` — Rate limiting

| Column | Type | Notes |
|---|---|---|
| `id_login_attempt` | INT (PK) | Auto-increment |
| `email` | VARCHAR(150) | Unique, normalized (lowercase) |
| `failed_attempts` | INT | Counter, default 0 |
| `locked_until` | DATETIME | Nullable; set after 5 failures |
| `last_attempt` | TIMESTAMP | Auto-updated |

---

## Project Structure

```
Blog_Tanger_vibes_V2/
├── config/
│   └── connection.php              PDO database connection
├── sql/
│   └── script.sql                  Database schema + seed data
├── lang/
│   ├── en.php                      English translations (58+ keys)
│   ├── fr.php                      French translations (58+ keys)
│   └── ar.php                      Arabic translations (58+ keys)
├── includes/
│   ├── security.php                CSRF, session timeout, upload validation, security headers
│   ├── lang.php                    Translation loader, language switcher
│   ├── header.php                  Responsive nav, search, auth links, language dropdown
│   ├── footer.php                  Footer with dynamic categories
│   ├── auth_modal.php              Login/register modal with AJAX + inline JS
│   ├── pagination.php              Page-number pagination component
│   ├── actions.php                 Admin approve handler (POST only)
│   └── ajax_auth.php               AJAX login/register endpoint + rate limiting
├── pages/
│   ├── index.php                   Homepage with hero + 3 latest posts
│   ├── explore.php                 All published posts with search, filter, pagination
│   ├── detail.php                  Post detail, comments, map, share links
│   ├── about.php                   About page with live stats
│   ├── contact.php                 Contact form, info cards, FAQ, map
│   ├── dashboard.php               User/admin dashboard with stats + post management
│   ├── add_post.php                Create post form with image upload
│   ├── edit.php                    Edit post form with pre-filled data
│   ├── delete.php                  Post delete handler (POST only)
│   ├── reject.php                  Admin rejection form
│   └── logout.php                  Session destroy + cookie cleanup
├── assets/
│   ├── css/
│   │   ├── main.css                Global styles + CSS custom properties
│   │   ├── header.css              Navigation, search, language switcher
│   │   ├── footer.css              Footer layout
│   │   ├── home.css                Hero + latest posts
│   │   ├── cards.css               Post card component
│   │   ├── explor.css              Explore page filters + grid
│   │   ├── detail.css              Post detail + comments
│   │   ├── dashboard.css           Dashboard stats, table, modals
│   │   ├── add_post.css            Post editor form
│   │   ├── reject.css              Rejection form
│   │   ├── about.css               About page layout
│   │   ├── contact.css             Contact form + FAQ
│   │   ├── components.css          Shared alerts, pagination, form focus
│   │   └── rtl.css                 Right-to-left overrides
│   ├── js/
│   │   ├── main.js                 Mobile nav toggle, language dropdown
│   │   ├── add_post.js             Image preview
│   │   └── contact.js              FAQ accordion
│   ├── images/
│   │   ├── logo.png                Site logo
│   │   ├── home.jpg                Hero background (fallback)
│   │   ├── home_480.jpg            Responsive hero (480w)
│   │   ├── home_768.jpg            Responsive hero (768w)
│   │   ├── home_1200.jpg           Responsive hero (1200w)
│   │   └── home_1920.jpg           Responsive hero (1920w)
│   └── uploads/                    User-uploaded post images
└── README.md
```

---

## Installation

### Prerequisites

- Web server (Apache / Nginx) with PHP 7.4+
- MySQL or MariaDB server
- PHP PDO MySQL extension enabled

### Steps

1. Clone the repository into your web server's document root.

2. Create the database by importing `sql/script.sql`:
   ```bash
   mysql -u root -p < sql/script.sql
   ```

3. Configure the database connection in `config/connection.php`:
   ```php
   $db_host = "localhost";
   $db_name = "tangier_blog";
   $db_user = "root";
   $db_pass = "";
   ```

4. Ensure the `assets/uploads/` directory is writable by the web server for image uploads.

5. Create an admin account:
   - Register a new user through the "Get Started" modal on the homepage.
   - Promote the user in MySQL:
     ```sql
     UPDATE users SET role = 'admin' WHERE email = 'your@email.com';
     ```

6. Access the platform in your browser.

---

## Configuration

| Setting | Location | Default |
|---|---|---|
| Database credentials | `config/connection.php` | `localhost`, `tangier_blog`, `root`, empty password |
| Upload max size | `includes/security.php` | 50 MB (subject to `php.ini` limits) |
| Session timeout | `includes/security.php` | 1800 seconds (30 minutes) |
| Rate limit attempts | `includes/ajax_auth.php` | 5 failed attempts |
| Rate limit lockout | `includes/ajax_auth.php` | 10 minutes |
| Language | `includes/lang.php` | English (default), `?lang=fr` or `?lang=ar` to switch |
| Pagination per page | `pages/explore.php` | 6 posts |

---

## Screenshots

<!-- Screenshots can be added here -->
<!-- ![Homepage](screenshots/homepage.png) -->
<!-- ![Explore Page](screenshots/explore.png) -->
<!-- ![Post Detail](screenshots/detail.png) -->
<!-- ![Dashboard](screenshots/dashboard.png) -->
<!-- ![Admin Moderation](screenshots/moderation.png) -->

---

## Future Enhancements

- **User Profile Management** — Allow users to update their name, email, and password
- **Password Reset** — Email-based recovery flow
- **Rich Text Editor** — Replace plain textarea with a WYSIWYG editor
- **Analytics Dashboard** — Charts for posting activity and category popularity
- **Email Notifications** — Alerts when posts are approved or rejected
- **Social Authentication** — Google and other OAuth providers

---

## License

© 2026 Oussama Ech-charef. All rights reserved.

---

## Author

**Oussama Ech-charef**

Tangier Vibes — A personal project built to explore PHP, MySQL, and full-stack web development.
