
# StackOverflow Clone - Collaborative PHP Project

Welcome to our StackOverflow Clone project! This repository contains a PHP-based Q&A platform similar to StackOverflow, where users can sign up, log in, post questions, and provide answers.

This README is a complete guide for our team to set up the project locally, collaborate using Git and GitHub, and develop new features like side navigation, question posting, and answering.

---

## Table of Contents

- [Project Setup](#project-setup)
- [Database Setup](#database-setup)
- [Project Structure](#project-structure)
- [Using Git and Collaboration Workflow](#using-git-and-collaboration-workflow)
- [Adding Features & Side Navigation Bar](#adding-features--side-navigation-bar)
- [Coding Guidelines](#coding-guidelines)
- [Troubleshooting & Support](#troubleshooting--support)
- [Contact](#contact)

---

## Project Setup

### 1. Clone the repository

```bash
git clone https://github.com/SCRCR7/stackoverflow_clone.git
```

### 2. Navigate into the project directory

```bash
cd stackoverflow_clone
```

### 3. Set up your local development environment

- Install and run a local server stack such as **XAMPP**, **WAMP**, or **MAMP**.
- Start Apache and MySQL services.
- Place the project folder inside your server’s root directory (`htdocs` or `www`).

### 4. Create and configure the database

Refer to [Database Setup](#database-setup).

### 5. Update database configuration

Edit the `config.php` file to set your database credentials:

```php
<?php
$host = 'localhost';
$db   = 'stackoverflow_clone';
$user = 'root';
$pass = '';
?>
```

---

## Database Setup

Run the following SQL commands to create the database and tables:

```sql
CREATE DATABASE stackoverflow_clone;

USE stackoverflow_clone;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## Project Structure

```
/stackoverflow_clone
  |-- /css/              # Stylesheets
  |-- /js/               # JavaScript files
  |-- /includes/         # Reusable parts (header, footer, sidebar)
  |-- /pages/            # Page files (login.php, signup.php, questions.php, etc.)
  |-- config.php         # Database connection config
  |-- index.php          # Homepage
  |-- README.md          # This file
```

---

## Using Git and Collaboration Workflow

### 1. Always pull latest changes before starting work

```bash
git pull origin main
```

### 2. Create a feature branch for your work

```bash
git checkout -b feature/your-feature-name
```

Example:

```bash
git checkout -b feature/side-navbar
```

### 3. Work on your changes locally.

### 4. Commit your changes with a clear message

```bash
git add .
git commit -m "Add side navigation bar"
```

### 5. Push your branch to GitHub

```bash
git push origin feature/your-feature-name
```

### 6. Create a Pull Request on GitHub

- Open a PR from your feature branch to `main`.
- Wait for review and approval.
- Merge when approved.

---

## Adding Features & Side Navigation Bar

### Side Navigation Bar Example

Add this HTML code inside your layout or sidebar include file (`includes/sidebar.php`):

```html
<nav class="sidebar">
  <ul>
    <li><a href="pages/questions.php">Questions</a></li>
    <li><a href="pages/answers.php">Answers</a></li>
    <li><a href="pages/create-question.php">Create Question</a></li>
    <li><a href="pages/profile.php">Profile</a></li>
    <li><a href="pages/logout.php">Logout</a></li>
  </ul>
</nav>
```

Style it with CSS to fit the UI design.

---

## Coding Guidelines

- Use PHP and MySQL for backend and data storage.
- Always sanitize and validate user inputs to prevent security risks.
- Use prepared statements for all database queries.
- Write reusable and modular code (use includes for headers, footers, navbars).
- Comment your code clearly.
- Test your changes locally before pushing.

---

## Troubleshooting & Support

- **Merge Conflicts:** If conflicts occur during `git pull`, manually resolve them in your editor, then commit.
- **Database Connection Errors:** Check your `config.php` and that MySQL service is running.
- **Page Loading Issues:** Ensure Apache server is running and your files are in the correct directory.
- For other issues, open GitHub Issues or ask in the team chat.

---

## Contact

For questions or help, reach out to:

- Sohaib Hassan (Project Lead)  
- Friend 1 Name  
- Friend 2 Name  

---

## Happy coding! 🚀

Let’s build an awesome StackOverflow clone together!

---

*This README was created to help our team collaborate effectively and keep our project organized.*
