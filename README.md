# manxa-scraper-backend

This project is a backend API for a man(x)a (g, hw, hu) reading application. It includes user authentication, favorites management, custom list support, reading progress tracking, and scraping functionality for manxa content.



## 📄 Documentation

For the full API documentation and implementation details, see [docs/api.md](docs/api.md).



## 📁 Project Structure

```
api/
├── chapter-progress/
│   ├── markRead.php           # Mark a chapter as read
│   ├── unmarkRead.php         # Unmark a chapter
│   └── getReadChapters.php    # Get list of read chapters per manxa
├── favorites/
│   ├── add.php                # Add a manxa to a favorites list
│   ├── get.php                # Get all favorites from a list
│   └── remove.php             # Remove a manxa from favorites
├── lists/
│   ├── add.php                # Create a new list
│   ├── get.php                # Get all lists of a user
│   ├── rename.php             # Rename a list
│   └── remove.php             # Delete a list
├── chapter.php                # Scrape chapter list from a manxa page
├── getProfile.php             # Get the logged-in user's profile info
├── imageProxy.php             # Proxy image URLs
├── login.php                  # User login and JWT token generation
├── manxa.php                  # Scrape manxa info
├── manxaList.php              # Scrape manxa list
├── register.php               # Register a new user
src/
├── db.php                     # Database connection
├── jwtUtils.php               # JWT generation and validation
└── Scraper.php                # Manxa scraping logic
```



## 🔐 JWT Auth

Most endpoints require a valid JWT in the `Authorization` header:

```
Authorization: Bearer YOUR_TOKEN
```

You can use helper methods in `src/jwtUtils.php` to generate and verify tokens.



## 🛠 Database Notes

* All tables use `ON DELETE CASCADE` for relational integrity.
* UUIDs or auto-increment integers used as primary keys.
* `manxa_url` and `chapter_url` are used as unique references for manxa and chapters.
* Default list `"Favorites"` is created during registration.



## 📦 Setup

1. Create a `.env` file with DB and secret key info:

```
DB_HOST=localhost
DB_NAME=manxa
DB_USER=root
DB_PASS=secret
JWT_SECRET=your_super_secret_key
```

2. Set up your MySQL tables see [docs/db.md](docs/db.md).
3. Run with PHP’s built-in server:

```
php -S localhost:8000
```

---

