
## 🧑‍💻 User Authentication

### `POST /api/register.php`

Registers a new user and creates a default list ("Standard").

**Request JSON:**

```json
{
  "email": "user@example.com",
  "password": "yourPassword"
}
```

**Response:**

```json
{
    "success": true,
    "message": "User registered successfully."
}
```



### `POST /api/login.php`

Authenticates user and returns a JWT.

**Request JSON:**

```json
{
  "email": "user@example.com",
  "password": "yourPassword"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Login successful.",
    "token": "JWT_TOKEN_HERE"
}
```



## 👤 Profile

### `GET /api/getProfile.php`

Requires JWT.

**Headers:**

```
Authorization: Bearer <JWT_TOKEN>
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "email": "user@example.com",
        "created_at": "creation_timestamp"
    }
}
```



## 📚 Custom Lists

### `POST /api/lists/add.php`

Creates a new list for the user.

**Request JSON:**

```json
{
    "name": "My Manxa List"
}
```

**Response:**

```json
{
    "success": true,
    "message": "List created."
}
```



### `GET /api/lists/get.php`

Returns all user-defined lists.

**Response:**

```json
{
    "success": true,
    "lists": [
        {
            "name": "Standard",
            "created_at": "creation_timestamp"
        },
        {
            "name": "Read Later",
            "created_at": "creation_timestamp"
        }
    ]
}
```



### `POST /api/lists/rename.php`

Renames a list.

**Request JSON:**

```json
{
  "old_name": "Standard",
  "new_name": "Classic"
}
```

**Response:**

```json
{
    "success": true,
    "message": "List renamed."
}
```



### `POST /api/lists/remove.php`

Deletes a list.

**Request JSON:**

```json
{
    "name": "Read Later"
}
```

**Response:**

```json
{
    "success": true,
    "message": "List deleted."
}
```



## ⭐ Favorites

### `POST /api/favorites/add.php`

Adds a manxa to a favorites list. If `list_name` is not provided, defaults to `"Standard"`.

**Request JSON:**

```json
{
  "title": "One Piece",
  "manxa_url": "/manxa/one-piece",
  "list_name": "Read Later" // optional
}
```

**Response:**

```json
{
    "success": true,
    "message": "Manxa added to list_name."
}
```



### `GET /api/favorites/get.php?list=list_name`

Returns all favorites from list list_name.

**Response:**

```json
[
  {
    "list": "Standard",
    "items": {
        "success": true,
        "favorites": [
            {
                "title": "Naruto",
                "manxa_url": "/manxa/naruto",
                "created_at": "creation_timestamp"
            },
            {
                "title": "One Piece",
                "manxa_url": "/manxa/one-piece",
                "created_at": "creation_timestamp"
            }
        ]
    }
  }
]
```



### `POST /api/favorites/remove.php`

Removes a manxa from a list. If `list_name` is not provided, defaults to `"Standard"`.

**Request JSON:**

```json
{
  "manxa_url": "/manxa/naruto",
  "list_name": "Standard" // optional
}
```

**Response:**

```json
{
    "success": true,
    "message": "Manxa removed from list_name."
}
```



## ✅ Reading Progress

### `POST /api/chapter-progress/markRead.php`

Marks a chapter as read.

**Request JSON:**

```json
{
  "manxa_url": "/manxa/bleach",
  "chapter_url": "/manxa/bleach/chapter-123"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Chapter marked as read."
}
```



### `POST /api/chapter-progress/unmarkRead.php`

Unmarks a chapter.

**Request JSON:**

```json
{
  "manxa_url": "/manxa/bleach",
  "chapter_url": "/manxa/bleach/chapter-123"
}
```

**Response:**

```json
{
    "success": true,
    "message": "Chapter unmarked as read."
}
```



### `GET /api/chapter-progress/getReadChapters.php?manxa_url=/manxa/bleach`

Returns list of chapter URLs marked as read.

**Response:**

```json
{
    "success": true,
    "read_chapters": [
        "/manxa/bleach/chapter-123",
        "/manxa/bleach/chapter-124"
    ]
}
```



## 🔍 Scraper Endpoints

### `GET /api/manxa.php?title=manxa_title`

Scrapes homepage manxa listings.



### `GET /api/manxaList.php?page=1`

Scrapes manxa listing.



### `GET /api/chapter.php?chapter=/manxa/naruto/chapter-123`

Returns a chapter imgs.



### `GET /api/imageProxy.php?url=https://...`

Image proxy (used if CORS issues with images arise).

