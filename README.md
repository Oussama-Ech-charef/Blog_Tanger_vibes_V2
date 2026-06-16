<div align="center">

# 🧭 Tangier Vibes

**A Tourism Blog & City Guide Platform for Tangier, Morocco**

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Academic Project](https://img.shields.io/badge/Academic%20Project-PFE-0a7ea4?style=for-the-badge)

</div>

---

## 📋 Table of Contents

- [Project Overview](#-project-overview)
- [Objectives](#-objectives)
- [Main Features](#-main-features)
- [Technologies Used](#-technologies-used)
- [Security Features](#-security-features)
- [Database Structure](#-database-structure)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Usage](#-usage)
- [User Roles](#-user-roles)
- [Future Improvements](#-future-improvements)
- [Author](#-author)
- [License](#-license)

---

## 📖 Project Overview

Tangier Vibes is a tourism blog and city guide platform built with PHP and MySQL. It helps users discover places, culture, restaurants, beaches, hotels, and attractions in Tangier, Morocco. The system provides a content management workflow where authors create posts and administrators moderate the publication process through a role-based access model.

---

## 🎯 Objectives

- Provide a digital platform showcasing the diverse attractions of Tangier
- Enable registered users to contribute content through a structured post creation workflow
- Implement a content moderation system with admin review, approval, and rejection
- Offer intuitive browsing with category-based filtering and detailed place information
- Deliver a responsive web interface accessible on desktop and mobile devices

---

## 🚀 Main Features

| Feature | Description |
|---------|-------------|
| **User Registration & Login** | Account creation with name, email, and hashed password; session-based authentication |
| **Role Management** | Two roles (`user` / `admin`) with distinct permissions enforced across the system |
| **Post Creation** | Form with title, category selection, content body, and optional image upload |
| **Post Editing** | Pre-filled form allowing authors to modify their own posts |
| **Post Deletion** | Owners can delete their posts; admins can delete any post |
| **Dashboard** | Central hub with stats cards (total, published, pending, draft, rejected) and post management table with modals |
| **Categories** | Six predefined categories: Beaches, Food & Restaurants, Culture & History, Nature & Parks, Hotels & Riads, Nightlife |
| **Explore Page** | Grid of published posts with category filter buttons |
| **Image Upload** | Timestamp-based filenames stored in `assets/uploads/` |
| **Publication Workflow** | Four-status lifecycle: `draft` → `pending` → `published` / `rejected` |
| **Admin Moderation** | Approve pending posts or reject them with a required textual reason |
| **Rejection Feedback** | Users view rejection reasons through a modal window on the dashboard |
| **Google Maps** | Fixed embedded iframe centered on Tangier, displayed on every post detail page |
| **Responsive Design** | CSS media queries adapt layout across desktop, tablet, and mobile; slide-in mobile navigation menu |

---

## 🛠 Technologies Used

| Technology | Purpose |
|------------|---------|
| **PHP** | Server-side scripting and business logic |
| **MySQL** | Relational database for data persistence |
| **PDO** | Secure database access with prepared statements |
| **HTML5** | Semantic page structure |
| **CSS3** | Styling with custom properties, Flexbox, and CSS Grid |
| **JavaScript** | Mobile menu toggle and deletion confirmation dialogs |
| **Font Awesome** | Icon library used across the interface |
| **Google Maps Embed API** | Fixed embedded map centered on Tangier shown on post detail pages |
| **Google Fonts (Inter)** | Primary typography across the platform |

---

## 🔒 Security Features

| Mechanism | Implementation |
|-----------|---------------|
| **Password Hashing** | Passwords hashed with `password_hash(PASSWORD_DEFAULT)` on registration and verified with `password_verify()` on login |
| **PDO Prepared Statements** | All database queries use parameterized prepared statements, preventing SQL injection |
| **Session Authentication** | User sessions created with `session_regenerate_id(true)` after login; protected pages check `$_SESSION['id_user']` before rendering |
| **Role-Based Access Control** | Admin-only actions check `$_SESSION['role'] === 'admin'`; post ownership verified before edit or delete |
| **XSS Prevention** | User output escaped with `htmlspecialchars()` throughout all views |
| **Input Validation** | Email format validation (`FILTER_VALIDATE_EMAIL`), password minimum length (6 characters), and required field checks on all forms |

---

## 🗄 Database Structure

The database `tangier_blog` contains three tables with the following relationships:

```
users ──────┬── posts (id_user)         → author
            └── posts (id_approved_by)  → reviewer (nullable)

categories ──┬── posts (id_category)
```

<details>
<summary><strong>users</strong> — Registered accounts</summary>

| Column | Type | Notes |
|--------|------|-------|
| `id_user` | INT (PK) | Auto-increment |
| `user_name` | VARCHAR(100) | Full name |
| `email` | VARCHAR(150) | Unique |
| `password` | VARCHAR(255) | Hashed |
| `role` | ENUM('user','admin') | Default `user` |
| `created_at` | TIMESTAMP | Auto-generated |

</details>

<details>
<summary><strong>categories</strong> — Post classification</summary>

| Column | Type | Notes |
|--------|------|-------|
| `id_category` | INT (PK) | Auto-increment |
| `cat_name` | VARCHAR(100) | Unique |
| `created_at` | TIMESTAMP | Auto-generated |

Default rows: Beaches, Food & Restaurants, Culture & History, Nature & Parks, Hotels & Riads, Nightlife.
</details>

<details>
<summary><strong>posts</strong> — Blog content</summary>

| Column | Type | Notes |
|--------|------|-------|
| `id_post` | INT (PK) | Auto-increment |
| `id_category` | INT (FK) | → categories (`ON DELETE CASCADE`) |
| `id_user` | INT (FK) | → users (`ON DELETE CASCADE`) |
| `id_approved_by` | INT (FK) | → users, nullable (`ON DELETE SET NULL`) |
| `title` | VARCHAR(255) | Post title |
| `image` | VARCHAR(255) | File path, nullable |
| `content` | TEXT | Post body |
| `status` | ENUM('draft','pending','published','rejected') | Publication state |
| `rejection_reason` | TEXT | Admin feedback, nullable |
| `approved_at` | TIMESTAMP | Nullable |
| `created_at` | TIMESTAMP | Auto-generated |
| `updated_at` | TIMESTAMP | Auto-updated on modification |

</details>

---

## 📁 Project Structure

```
├── config/
│   └── connection.php            PDO database connection
├── pages/
│   ├── index.php                 Homepage with hero + 3 latest published posts
│   ├── explore.php                Explore page with category filtering
│   ├── detail.php                Post detail with map, share links, and comments UI
│   ├── login.php                 User authentication form
│   ├── register.php              New user registration form
│   ├── logout.php                Session destroy and redirect
│   ├── dashboard.php             Stats, post table, view/approve/reject modals
│   ├── add_post.php              Create post form with image upload
│   ├── edit.php                 Edit post form with pre-filled data
│   ├── delete.php                 Delete post handler with role-based permissions
│   └── reject.php                Admin rejection form with reason input
├── includes/
│   ├── header.php                Responsive navigation with session-aware links
│   ├── footer.php                Logo, quick links, category links, social icons
│   └── actions.php               Admin approval action handler
├── assets/
│   ├── css/                      Ten stylesheets (main, header, footer, home, explor, detail, dashboard, auth, add_post, reject)
│   ├── js/
│   │   └── main.js               Mobile menu toggle
│   ├── images/                   Static assets (hero background)
│   └── uploads/                  User-uploaded post images
└── sql/
    └── script.sql               Database schema and default category data
```

---

## ⚙ Installation

### Prerequisites

- Web server (Apache / Nginx) with PHP 7.4+
- MySQL or MariaDB server
- PHP PDO MySQL extension enabled

### Steps

1. Clone or download the project into your web server's document root.

2. Create the database by running `sql/script.sql` in your MySQL client.

3. Configure the database connection in `config/connection.php`:
   ```php
   $host = "localhost";
   $db_name = "tangier_blog";
   $username = "root";
   $password = "";
   ```

4. Ensure the `assets/uploads/` directory is writable by the web server for image uploads.

5. Create an admin account:
   - Register a new user through the registration page (`pages/register.php`).
   - Promote the user in MySQL:
     ```sql
     UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
     ```

6. Access the platform at [https://tanger.lovestoblog.com/](https://tanger.lovestoblog.com/).

---

## 💡 Usage

| Role | Capabilities |
|------|-------------|
| **Visitor** | Browse published posts on the home and explore pages; view post details with the embedded map and comments section |
| **User** | All visitor features plus dashboard access; create, edit, and delete own posts; view rejection reasons |
| **Admin** | All user features plus full post visibility across all authors; approve or reject pending posts; publish without moderation; delete any post |

---

## 👥 User Roles

| Permission | User | Admin |
|------------|:----:|:-----:|
| Browse published content | ✅ | ✅ |
| Register and login | ✅ | ✅ |
| Create posts | ✅ | ✅ |
| Edit own posts | ✅ | ✅ |
| Delete own posts | ✅ | ✅ |
| View own post status | ✅ | ✅ |
| View rejection reason | ✅ | ✅ |
| See all users' posts | ❌ | ✅ |
| Approve pending posts | ❌ | ✅ |
| Reject posts with reason | ❌ | ✅ |
| Delete any post | ❌ | ✅ |
| Publish without moderation | ❌ | ✅ |

---

## 🔮 Future Improvements

- **Comment System** — Backend logic to store and retrieve comments (UI already built on the detail page)
- **Password Reset** — Email-based recovery flow (link present on the login page)
- **Search Functionality** — Connect the search bar to full-text database queries
- **User Profile Management** — Page for users to update their name, email, and password
- **Pagination** — Handle growing post volumes on the explore page and dashboard table
- **Rich Text Editor** — Replace the plain textarea with a WYSIWYG editor for formatted content
- **Analytics Dashboard** — Charts for posting activity and category popularity
- **Email Notifications** — Alerts sent to users when their posts are approved or rejected

---

## 👤 Author

**Oussama Ech-charef**

Final Year Project (PFE) developed at **Solicode**.

Tangier Vibes is an academic web development project created as part of the Solicode training program.

---

## 📄 License

This project was developed for educational and academic purposes only as a Final Year Project (PFE) at Solicode.

© 2026 Oussama Ech-charef. All rights reserved.
