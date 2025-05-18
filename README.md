# StackOverflow Clone

A modern, full-featured Q&A platform inspired by [Stack Overflow](https://stackoverflow.com/), built primarily using PHP and MySQL. This project allows users to post questions, submit answers, manage profiles, earn reputation, and more—packed into a beautiful and responsive web app!

---

## 🚀 Features

- **User Authentication:** Secure sign-up, login, and logout.
- **Ask & Answer:** Post questions, answer others, and vote.
- **Tags & Search:** Tag questions and search by tags.
- **Reputation System:** Earn points and badges for contributions.
- **User Profiles:** Manage your details, see your questions, answers, and reputation.
- **Responsive UI:** Clean, mobile-friendly design.
- **Articles & Jokes:** Share articles and lighten up with some programming jokes!
- **Bounties & Badges:** Reward and recognize outstanding contributions.

---

## 📸 Preview

![Screenshot](https://cdn.sstatic.net/Sites/stackoverflow/Img/logo.png)

---

## 🛠️ Getting Started

### 1. **Clone the Repository**

```bash
git clone https://github.com/SCRCR7/stackoverflow_clone.git
cd stackoverflow_clone
```

### 2. **Setup the Database**

- Import the provided SQL file (`database.sql`) into your MySQL server using phpMyAdmin or the MySQL command line.
    - With phpMyAdmin: Click "Import", select `database.sql`, and execute.
    - With command line:
      ```bash
      mysql -u your_username -p your_database < database.sql
      ```
- Update database connection credentials in `connect.php`:

```php
$host = 'localhost';
$db   = 'your_database';
$user = 'your_username';
$pass = 'your_password';
```

### 3. **Run Locally**

- Place the project folder (e.g., `stackoverflow_clone`) into your local server directory (`htdocs` for XAMPP or `www` for WAMP).
- Start Apache and MySQL from your server control panel.
- Visit [http://localhost/stackoverflow_clone](http://localhost/stackoverflow_clone) in your web browser.

---

## 🧑‍💻 **How to Use This Project**

1. **Sign Up:**  
   Go to the Sign Up page and create a new account with your email, username, and a secure password.

2. **Login:**  
   Use your credentials to log in and access all features.

3. **Ask a Question:**  
   Click on the "Questions" or "Ask Question" button, fill in your question, add relevant tags, and submit.

4. **Browse & Answer:**  
   Browse existing questions, click on any to read details, and provide your answers.

5. **Vote and Earn Reputation:**  
   Upvote helpful questions and answers; your posts can be upvoted too! Accumulate points and earn badges.

6. **Explore More:**  
   Check out Tags, Articles, Jokes, Bounties, and more from the navigation menu.

7. **Profile Management:**  
   View and edit your profile, see your stats, and track your reputation.

8. **Logout:**  
   Use the logout button on the top right to securely exit your session.

---

## 💡 Folder Structure

```
p1/
├── answers.php
├── articles.php
├── badges.php
├── bounties.php
├── connect.php
├── header.php
├── index.php
├── jokes.php
├── login.php / login.html
├── logout.php
├── profile.php
├── questions.php
├── reputation.php
├── signup.php / signup.html
├── style.css
├── tags.php
└── database.sql
```

---

## 🙌 Contributors

A huge thanks to everyone who made this project possible:

- **Sohaib Hassan**
- **Muhammad Nauman**
- **Bilal Khalil**
- **SCRCR7** (Maintainer)

---

## 🤝 Contributing

Pull requests and stars are welcome! For major changes, please open an issue first.

---

## 📄 License

This project is open-source and free to use for educational purposes.

---

> Built with ❤️ by Sohaib Hassan, Muhammad Nauman, Bilal Khalil, and maintained by SCRCR7.
