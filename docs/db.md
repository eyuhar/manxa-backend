

## 🚠 Database Schema

### `users`

```sql
id INT PRIMARY KEY AUTO_INCREMENT,
email VARCHAR(255) UNIQUE NOT NULL,
password VARCHAR(255) NOT NULL
```

### `lists`

```sql
id INT PRIMARY KEY AUTO_INCREMENT,
user_id INT NOT NULL,
name VARCHAR(255) NOT NULL,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

### `favorites`

```sql
id INT PRIMARY KEY AUTO_INCREMENT,
user_id INT NOT NULL,
list_id INT NOT NULL,
title VARCHAR(255) NOT NULL,
manxa_url TEXT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE
```

### `chapter_progress`

```sql
id INT PRIMARY KEY AUTO_INCREMENT,
user_id INT NOT NULL,
manxa_url VARCHAR(255) NOT NULL,
chapter_url VARCHAR(255) NOT NULL,
read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
UNIQUE KEY (user_id, manxa_url, chapter_url),
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

---

