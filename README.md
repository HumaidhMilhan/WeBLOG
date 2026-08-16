# WeBLOG

WeBLOG is a blog application created for the IN2120 Web Programming assignment. It uses HTML, CSS, PHP, and MySQL.

## Phase 1

- User registration
- User login
- User logout
- Password hashing
- Session authentication

## Phase 2

- Public blog list
- Public single blog page
- Create blogs while logged in
- Edit and delete only your own blogs
- Blog ownership checks

## Phase 3

- Responsive frontend pages
- Blog editor validation with JavaScript
- Delete confirmation with JavaScript

## Final Features

- Markdown editor with a formatting toolbar and live preview
- Bold, italic, underline, headings, lists, and safe hyperlinks
- 3000-character blog limit
- Login validation in PHP and JavaScript
- Public comments for blog posts
- Comment deletion by the comment author or blog owner
- Responsive homepage with blog excerpts and comment counts

## Project Structure

- `backend/api` contains the form handlers.
- `backend/config` contains the database connection files.
- `database` contains the database schema.
- `frontend/pages` contains the application pages.
- `frontend/css` contains the stylesheet.

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

3. Copy `backend/config/db.example.php` to `backend/config/db.php` and enter the database details.

   Existing installations should also import `database/add_comments.sql`.

4. Place the project in the web server directory and open `index.php` in a browser.

## Deployment

The hosted application is available at [https://humaidh.alwaysdata.net](https://humaidh.alwaysdata.net). Deployment steps are in [docs/deployment.md](docs/deployment.md).
