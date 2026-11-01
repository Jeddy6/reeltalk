
# Chirply

Chirply is a mini social media web application inspired by platforms where users can share posts, interact with others, and explore movies.

## Features

- User registration and login
- Create and delete posts
- Like posts and add comments
- User profiles and profile pictures
- Follow other users
- Movie list and movie reviews
- Personal movie watchlist
- PHP MVC structure
- MySQL database integration

## Technologies Used

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP for local development

## Project Structure

```text
chirply/
├── app/
│   ├── controllers/
│   ├── models/
│   └── views/
├── config/
│   ├── database.php
│   └── database.example.php
├── public/
│   ├── index.php
│   └── uploads/
├── uploads/
│   ├── posts/
│   └── profiles/
├── index.php
├── .gitignore
└── README.md
```

## Local Setup

1. Install XAMPP and start Apache and MySQL.
2. Place the project folder inside `xampp/htdocs`.
3. Create a MySQL database for Chirply using phpMyAdmin.
4. Configure your database connection in `config/database.php`.
5. Import the project's database tables and required data.
6. Open the project through your local XAMPP server.

## Security Notes

- Never publish real database credentials.
- Keep uploaded user content and private configuration out of GitHub.
- Use `config/database.example.php` as a template for configuration.

## Live Website

https://chirply.infinityfree.io/
