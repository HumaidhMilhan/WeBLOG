# WeBLOG

WeBLOG is a lightweight, responsive Blog Application built as part of the IN2120 Web Programming assignment. The project implements a secure authentication flow and role-based access control, allowing users to read, write, and manage blog posts.

## Tech Stack
- **Frontend**: Plain HTML5, responsive CSS3, and Vanilla JavaScript
- **Backend**: Plain PHP (Session-based, procedural style)
- **Database**: MySQL (using PHP Data Objects (PDO) for secure connection)

## Project Structure
- `/database`: Contains SQL schema definitions (`schema.sql`)
- `/backend`: Core server logic
  - `/config`: Database connection configurations (`db.php`)
  - `/api`: Registration, login, and logout backend handlers
- `/frontend`: User Interface
  - `/pages`: HTML and PHP views (registration, login, home feed, etc.)
  - `/css`: Custom styling sheets (`style.css`)
- `/docs`: Project instructions and assignment specifications

## Implemented Features (Phase 1)
- **User Registration**: Secure sign-up interface validate inputs, checks for existing usernames/emails, hashes passwords with `password_hash`, and stores users in the database.
- **User Login**: Session-based login system that validates credentials using `password_verify` and stores session state.
- **User Logout**: Fully clears session parameters and invalidates authentication cookies.
- **Simple Alerts**: Feedback alerts for registration success, errors, or invalid credentials.

## Installation & Setup

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd WeBLOG
   ```

2. **Configure Database**
   - Ensure you have MySQL running locally (e.g., via XAMPP, WAMP, or standalone).
   - Create a database named `weblog_db`.
   - Import the schema from `database/schema.sql` into your database:
     ```bash
     mysql -u root -p weblog_db < database/schema.sql
     ```

3. **Configure Database Connection**
   - Create or update the connection credentials in `backend/config/db.php`:
     ```php
     $host = 'localhost';
     $db = 'weblog_db';
     $user = 'your_mysql_username';
     $pass = 'your_mysql_password';
     ```

4. **Run the Application**
   - Place the project directory under your web server root (e.g., `htdocs` for Apache).
   - Open your browser and navigate to the application starting page, e.g., `http://localhost/WeBLOG/frontend/pages/login_view.php`.