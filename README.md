# WeBLOG

WeBLOG is a full-stack web application for creating, managing, and reading blog posts. It was originally developed as a project for the IN2120 Web Programming assignment and features a complete custom-built architecture using plain HTML, CSS, JavaScript, PHP, and MySQL.

## System Overview

The system is designed as a monolithic MVC-inspired application. It separates concerns into clear backend logic, frontend presentation, and data management.

### Key Features
- **User Authentication:** Secure registration and login system with password hashing and PHP session management.
- **Blog Management:** Authenticated users can create, edit, and delete their own markdown-formatted blog posts. Features a live-preview Markdown editor with a 3000-character limit.
- **Public Feed & Interactions:** Visitors can read public blog posts, and authenticated users can leave public comments on any blog. Comment authors or blog owners can delete comments.
- **Responsive UI:** A fully responsive frontend designed from scratch with custom CSS to provide an optimal reading experience on any device.

### Architecture & Codebase Structure
- **Frontend (`/frontend`)**: Contains the user interface and client-side logic.
  - **`pages/`**: PHP files that act as the views, blending HTML with PHP data for dynamic content rendering (e.g., `home.php`, `login_view.php`).
  - **`css/`** & **`js/`**: Custom stylesheets and JavaScript files for UI styling, responsive behavior, and client-side form validation (e.g., delete confirmations, editor live preview).
- **Backend (`/backend`)**: Handles server-side processing, form handling, and database interactions.
  - **`api/`**: Contains PHP scripts that act as endpoints for form submissions (e.g., registration, creating blogs, posting comments).
  - **`config/`**: Database connection configuration (`db.php`) and setup logic.
  - **`includes/`**: Shared backend utility functions or reusable server-side components.
- **Database (`/database`)**: Uses a relational MySQL database (`schema.sql`) with tables for `user`, `blogPost`, and `comment` to handle persistence, including relational constraints and cascading deletes.
- **Entry Point (`index.php`)**: Located at the project root, it acts as a simple redirect to the primary frontend homepage (`frontend/pages/home.php`).

## Installation & Setup

1. Clone the repository.
   ```text
   git clone <repository-url>
   cd WeBLOG
   ```

2. Create a MySQL database named `weblog_db` and import the schema.
   ```text
   mysql -u root -p weblog_db < database/schema.sql
   ```
   *(Note: Existing installations should also import `database/add_comments.sql` if updating from an older version).*

3. Copy `backend/config/db.example.php` to `backend/config/db.php` and configure it with your database credentials.

4. Place the project in a PHP-enabled web server directory (e.g., XAMPP's `htdocs`) and navigate to the project folder (`index.php`) in your browser.

## Deployment

The hosted application is available at [https://humaidh.alwaysdata.net](https://humaidh.alwaysdata.net). Additional deployment instructions can be found in `docs/deployment.md`.
