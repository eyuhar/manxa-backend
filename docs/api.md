## 🧑‍💻 User Authentication

### `POST /api/register`

Registers a new user and creates a default list ("Standard").

**Request JSON:**

```json
{
  "user_name": "User",
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

### `POST /api/login`

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

### `GET /api/profile`

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

### `POST /api/lists`

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

### `GET /api/lists`

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

### `PUT /api/lists`

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

### `DELETE /api/lists`

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

### `POST /api/favorites`

Adds a manxa to a favorites list. Accepts either a single object or an array of objects.
If `list_name` is not provided, defaults to `"Standard"`.

**Request JSON:**

```json
{
  "title": "TwoPunch-Man",
  "manxa_url": "/manxa/twopunch-man",
  "list_name": "Read Later" // optional
}
```

**Response:**

```json
{
  "results": [
    {
      "title": "TwoPunch-Man",
      "manxa_url": "/manxa/twopunch-man",
      "success": true,
      "status": 201,
      "message": "Manxa added to Read Later."
    }
  ]
}
```

### `GET /api/favorites?list=list_name`

Returns all favorites from list list_name.

**Response:**

```json
{
  "success": true,
  "list": "Standard",
  "favorites": [
    {
      "title": "TwoPunch-Man",
      "manxa_url": "/manxa/TwoPunch-Man",
      "created_at": "creation_timestamp"
    },
    {
      "title": "The Devil Butler",
      "manxa_url": "/manxa/the-devil-butler",
      "created_at": "creation_timestamp"
    }
  ]
}
```

### `DELETE /api/favorites`

Removes a manxa from a list. Accepts either a single object or an array of objects.
If `list_name` is not provided, defaults to `"Standard"`.

**Request JSON:**

```json
{
  "manxa_url": "/manxa/twopunch-man",
  "list_name": "Standard" // optional
}
```

**Response:**

```json
{
  "results": [
    {
      "manxa_url": "/manxa/twopunch-man",
      "list_name": "Standard",
      "success": true,
      "status": 200,
      "message": "Manxa removed from Standard."
    }
  ]
}
```

## ✅ Reading Progress

### `POST /api/chapter-progress`

Marks a chapter as read. Accepts either a single object or an array of objects.

**Request JSON:**

```json
{
  "manxa_url": "/manxa/kingdom",
  "chapter_url": "/manxa/kingdom/chapter-123"
}
```

**Response:**

```json
{
  "results": [
    {
      "manxa_url": "/manxa/kingdom",
      "chapter_url": "/manxa/kingdom/chapter-123",
      "success": true,
      "status": 201,
      "message": "Chapter marked as read"
    }
  ]
}
```

### `DELETE /api/chapter-progress`

Unmarks a chapter. Accepts either a single object or an array of objects.

**Request JSON:**

```json
{
  "manxa_url": "/manxa/kingdom",
  "chapter_url": "/manxa/kingdom/chapter-123"
}
```

**Response:**

```json
{
  "results": [
    {
      "manxa_url": "/manxa/kingdom",
      "chapter_url": "/manxa/kingdom/chapter-123",
      "success": true,
      "status": 200,
      "message": "Chapter unmarked as read."
    }
  ]
}
```

### `GET /api/chapter-progress?manxa_url=/manxa/kingdom`

Returns list of chapter URLs marked as read.

**Response:**

```json
{
  "success": true,
  "read_chapters": ["/manxa/kingdom/chapter-123", "/manxa/kingdom/chapter-124"]
}
```

## ✅ Reading History

### `GET /api/history`

Returns reading history.

**Response:**

```json
{
  "success": true,
  "history": [
    {
      "manxa_url": "manxa/kingdom",
      "chapter_url": "manxa/kingdom/chapter-123",
      "read_at": "Y-m-d H:i:s"
    }
  ]
}
```

## 🔍 Scraper Endpoints

### `GET /api/manxa?title=manxa_title`

Scrapes homepage manxa listings.

### `GET /api/manxas?query=query&page=1`

Scrapes manxa list. query and page parameters are optional.

### `GET /api/chapter?chapter=/manxa/naruto/chapter-123`

Returns chapter imgs.

### `GET /api/image-proxy?url=https://...`

Image proxy (used if CORS issues with images arise).
