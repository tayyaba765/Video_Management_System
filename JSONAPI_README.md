# JSON:API — How It All Fits Together

This document explains what every file in `app/JsonApi/V1/` does, what "Schema" and "Resource" mean, and what every route URL actually does.

---

## What is JSON:API?

JSON:API is a **standardised specification** for how REST APIs should format their JSON responses and accept requests. Instead of every developer inventing their own format, JSON:API defines rules for:

- How a response is structured (`data`, `attributes`, `relationships`, `meta`, `links`)
- How to paginate (`?page[number]=1&page[size]=10`)
- How to filter (`?filter[category]=Action`)
- How to sort (`?sort=title` or `?sort=-title` for descending)
- How to request only specific fields (`?fields[videos]=title,category`)
- How to load related data in one request (`?include=video-ratings`)

This project uses the **`laravel-json-api`** package which implements that spec on top of Laravel/Eloquent.

---

## Folder Structure

```
app/JsonApi/V1/
├── Server.php                      ← The server config (base URL, middleware, which schemas exist)
│
├── Videos/
│   ├── VideoSchema.php             ← Rules: what fields, filters, sorts are allowed
│   └── VideoResource.php           ← Presentation: how a Video model looks in JSON
│
├── VideoRatings/
│   ├── VideoRatingSchema.php       ← Rules for video_ratings
│   └── VideoRatingResource.php     ← Presentation of a VideoRating model
│
└── Users/
    ├── UserSchema.php              ← Rules for users
    └── UserResource.php            ← Presentation of a User model
```

---

## Server.php — The Entry Point

**File:** `app/JsonApi/V1/Server.php`

```php
protected string $baseUri = '/api/v1';
```

This is the **master config** for the entire JSON:API server. It does three things:

| Setting | What it controls |
|---|---|
| `$baseUri` | The URL prefix all routes live under (`/api/v1/...`) |
| `middleware()` | Which middleware runs on every JSON:API request (currently none — public) |
| `allSchemas()` | Registers VideoSchema, VideoRatingSchema, UserSchema |
| `authorizable()` | `false` = no policy/gate checks — all resources are publicly readable |

**Think of `Server.php` as the "reception desk"** — it directs every request to the right schema.

---

## What is a Schema?

A **Schema** is the **rulebook** for a resource. It answers:

- What **fields** (database columns) can be read or written through the API?
- Which fields are **sortable**?
- What **filters** are available?
- What **pagination** style is used?
- What **relationships** exist?

> A Schema does **not** control how data looks in the response — that is the Resource's job.

### VideoSchema.php

**File:** `app/JsonApi/V1/Videos/VideoSchema.php`
**Database table:** `videos`

#### Fields declared:

| Field in JSON | Column in DB | Notes |
|---|---|---|
| `id` | `id` | Auto, always included |
| `title` | `title` | Sortable: `?sort=title` |
| `youtubeId` | `youtube_id` | camelCase alias of snake_case column |
| `category` | `category` | Sortable: `?sort=category` |
| `description` | `description` | |
| `createdAt` | `created_at` | Read-only, sortable |
| `updatedAt` | `updated_at` | Read-only |
| `video-ratings` | HasMany → `ratings()` | Relationship, read-only |
| `users` | HasManyThrough → `users()` | Relationship |

#### Filters available:

| Query Parameter | What it does |
|---|---|
| `?filter[id][]=1&filter[id][]=2` | Return only videos with these IDs |
| `?filter[title]=Inception` | Exact title match |
| `?filter[category]=Action` | Exact category match |
| `?filter[search]=dark knight` | **LIKE search** across title, category, AND description |

The `search` filter calls `Video::scopeSearch()` — a custom scope on the model that does:
`WHERE title LIKE '%...%' OR category LIKE '%...%' OR description LIKE '%...%'`

#### Pagination:

Uses `PagePagination` — number/size style:

```
?page[number]=2&page[size]=10
```

Response includes a `meta.page` block:

```json
"meta": {
  "page": {
    "currentPage": 2,
    "from": 11,
    "lastPage": 4,
    "perPage": 10,
    "to": 20,
    "total": 35
  }
}
```

---

### VideoRatingSchema.php

**File:** `app/JsonApi/V1/VideoRatings/VideoRatingSchema.php`
**Database table:** `video_ratings`

#### Fields declared:

| Field in JSON | Column in DB | Notes |
|---|---|---|
| `id` | `id` | Auto |
| `score` | `score` | Integer 1–10 |
| `rating` | `rating` | Enum: hate/dislike/dissatisfied/neutral/satisfied/like/love |
| `review` | `review` | Nullable text |
| `createdAt` | `created_at` | Read-only, sortable |
| `updatedAt` | `updated_at` | Read-only |
| `video` | BelongsTo → `video()` | Which video was rated |
| `user` | BelongsTo → `user()` | Who rated it |

#### Filters available:

| Query Parameter | What it does |
|---|---|
| `?filter[id][]=5` | Specific rating by ID |
| `?filter[rating]=love` | Only ratings of type "love" |
| `?filter[video]=3` | Only ratings for video with ID 3 (matches `video_id` column) |

---

### UserSchema.php

**File:** `app/JsonApi/V1/Users/UserSchema.php`
**Database table:** `users`

#### Fields declared:

| Field in JSON | Column in DB | Notes |
|---|---|---|
| `id` | `id` | Auto |
| `name` | `name` | Sortable: `?sort=name` |
| `email` | `email` | Sortable: `?sort=email` |
| `createdAt` | `created_at` | Read-only, sortable |
| `updatedAt` | `updated_at` | Read-only |
| `video-ratings` | HasMany → `videoRatings()` | All ratings this user submitted |
| `videos` | HasManyThrough → `videos()` | All videos this user has rated |

> **Security note:** `password` and `remember_token` are never in the schema and will never appear in any JSON:API response.

#### Filters available:

| Query Parameter | What it does |
|---|---|
| `?filter[id][]=1&filter[id][]=2` | Return only specific users by ID |

---

## What is a Resource?

A **Resource** is the **presentation layer** — it controls exactly what the JSON looks like when a model is sent to the client.

Every JSON:API response wraps data in this shape:

```json
{
  "type": "videos",
  "id": "1",
  "attributes": {
    "title": "Inception",
    "category": "Science Fiction"
  },
  "relationships": {
    "video-ratings": {
      "links": { "related": "http://localhost:8000/api/v1/videos/1/video-ratings" }
    }
  },
  "links": {
    "self": "http://localhost:8000/api/v1/videos/1"
  }
}
```

The Resource class defines what goes inside `attributes` and `relationships`.

### VideoResource.php

Maps the `Video` Eloquent model → JSON attributes:

```php
'title'       => $this->resource->title,
'youtubeId'   => $this->resource->youtube_id,  // DB column renamed for JSON
'category'    => $this->resource->category,
'description' => $this->resource->description,
'createdAt'   => $this->resource->created_at,
'updatedAt'   => $this->resource->updated_at,
```

Relationship links generated:
- `video-ratings` → `/api/v1/videos/{id}/video-ratings`
- `users` → `/api/v1/videos/{id}/users`

### VideoRatingResource.php

Maps the `VideoRating` Eloquent model → JSON:

```php
'score'     => $this->resource->score,
'rating'    => $this->resource->rating,   // e.g. "love", "hate"
'review'    => $this->resource->review,
'createdAt' => $this->resource->created_at,
'updatedAt' => $this->resource->updated_at,
```

Relationship links:
- `video` → which video was rated
- `user` → who submitted the rating

### UserResource.php

Maps the `User` Eloquent model → JSON:

```php
'name'      => $this->resource->name,
'email'     => $this->resource->email,
'createdAt' => $this->resource->created_at,
'updatedAt' => $this->resource->updated_at,
```

Relationship links:
- `video-ratings` → all ratings this user submitted
- `videos` → all videos this user has rated

---

## Schema vs Resource — Quick Comparison

| | Schema | Resource |
|---|---|---|
| **Purpose** | Rules & capabilities | Presentation / formatting |
| **Controls** | Fields, filters, sorting, pagination | What the JSON attributes look like |
| **Analogy** | Database column definitions | A serialiser / transformer |
| **File** | `*Schema.php` | `*Resource.php` |

---

## Routes — What Each URL Does

Registered in `routes/api.php`. Each `$server->resource(...)` call automatically generates **8 standard routes**.

### Videos — `/api/v1/videos`

| Method | URL | What it does |
|---|---|---|
| `GET` | `/api/v1/videos` | List all videos (paginated) |
| `POST` | `/api/v1/videos` | Create a new video |
| `GET` | `/api/v1/videos/{id}` | Get one video by ID |
| `PATCH` | `/api/v1/videos/{id}` | Update a video |
| `DELETE` | `/api/v1/videos/{id}` | Delete a video |
| `GET` | `/api/v1/videos/{id}/video-ratings` | List all ratings for that video |
| `GET` | `/api/v1/videos/{id}/users` | List all users who rated that video |
| `GET` | `/api/v1/videos/{id}/relationships/video-ratings` | Relationship linkage (IDs only) |

**Query parameters for `GET /api/v1/videos`:**

```
?page[number]=1&page[size]=10          Paginate
?sort=title                            Sort A→Z by title
?sort=-createdAt                       Sort newest first
?filter[title]=Inception               Exact title match
?filter[category]=Action               Exact category match
?filter[search]=batman                 LIKE search (title + category + description)
?fields[videos]=title,category         Sparse fieldset — return only these fields
?include=video-ratings                 Sideload ratings in same response
```

**Example response for `GET /api/v1/videos?page[size]=2&sort=title`:**

```json
{
  "meta": {
    "page": { "currentPage": 1, "total": 8, "lastPage": 4, "perPage": 2 }
  },
  "links": {
    "first": "http://localhost:8000/api/v1/videos?page[number]=1&page[size]=2",
    "next":  "http://localhost:8000/api/v1/videos?page[number]=2&page[size]=2",
    "last":  "http://localhost:8000/api/v1/videos?page[number]=4&page[size]=2"
  },
  "data": [
    {
      "type": "videos",
      "id": "5",
      "attributes": {
        "title": "Avengers Endgame",
        "category": "Action",
        "youtubeId": "TcMBFSGVi1c",
        "description": "...",
        "createdAt": "2026-10-21T20:54:19.000000Z",
        "updatedAt": "2026-10-21T20:54:19.000000Z"
      },
      "relationships": {
        "video-ratings": {
          "links": {
            "related": "http://localhost:8000/api/v1/videos/5/video-ratings",
            "self":    "http://localhost:8000/api/v1/videos/5/relationships/video-ratings"
          }
        },
        "users": { "links": { ... } }
      },
      "links": { "self": "http://localhost:8000/api/v1/videos/5" }
    }
  ]
}
```

---

### Video Ratings — `/api/v1/video-ratings`

| Method | URL | What it does |
|---|---|---|
| `GET` | `/api/v1/video-ratings` | List all ratings (paginated) |
| `POST` | `/api/v1/video-ratings` | Create a rating |
| `GET` | `/api/v1/video-ratings/{id}` | Get one rating |
| `PATCH` | `/api/v1/video-ratings/{id}` | Update a rating |
| `DELETE` | `/api/v1/video-ratings/{id}` | Delete a rating |
| `GET` | `/api/v1/video-ratings/{id}/video` | Get the video that was rated |
| `GET` | `/api/v1/video-ratings/{id}/user` | Get the user who rated |

**Query parameters:**

```
?page[size]=5&sort=-createdAt          Latest ratings first
?filter[rating]=love                   Only "love" ratings
?filter[video]=3                       Only ratings for video ID 3
?include=video                         Sideload the video object
?include=user                          Sideload the user object
?include=video,user                    Sideload both
```

---

### Users — `/api/v1/users`

| Method | URL | What it does |
|---|---|---|
| `GET` | `/api/v1/users` | List all users |
| `GET` | `/api/v1/users/{id}` | Get one user |
| `GET` | `/api/v1/users/{id}/video-ratings` | All ratings by that user |
| `GET` | `/api/v1/users/{id}/videos` | All videos that user rated |

**Query parameters:**

```
?page[size]=10&sort=name               Paginate and sort alphabetically
?include=video-ratings                 Sideload their ratings
?include=videos                        Sideload the videos they rated
```

---

## Custom REST Routes — `routes/newapi.php`

These are **plain Laravel JSON routes** — not JSON:API spec. They live under `/api/` (no `v1`).

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/videos` | All videos with computed rating stats |
| `GET` | `/api/videos?q=term` | LIKE search across title + category |
| `GET` | `/api/videos/{id}/ratings` | All ratings for a video with user info |
| `POST` | `/api/ratings` | Create a rating (must be logged in) |
| `PUT` | `/api/ratings/{id}` | Update your own rating only |
| `DELETE` | `/api/ratings/{id}` | Delete your own rating only |

These return **richer computed data** that the JSON:API endpoints don't:

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Inception",
      "category": "Science Fiction",
      "ratings_count": 12,
      "avg_score": 8.3,
      "top_ratings": [
        { "rating": "love", "emoji": "❤️", "count": 5 },
        { "rating": "like", "emoji": "👍", "count": 4 }
      ],
      "user_rating": "love",
      "user_score": 9,
      "user_review": "Amazing film"
    }
  ]
}
```

---

## Web Auth Routes — `routes/web.php`

Browser/session-based routes — **not for API clients**:

| Method | URL | Middleware | Description |
|---|---|---|---|
| `GET` | `/login` | `guest` | Show login form |
| `POST` | `/login` | `guest` | Submit credentials, start session |
| `GET` | `/register` | `guest` | Show register form |
| `POST` | `/register` | `guest` | Create account, auto-login |
| `POST` | `/logout` | `auth` | Destroy session |
| `GET` | `/dashboard` | `auth` | Protected page (redirects if not logged in) |
| `GET` | `/video-ratings` | `auth` | Protected ratings view |

- The `guest` middleware redirects already-logged-in users away from login/register.
- The `auth` middleware redirects unauthenticated users to `/login`.

---

## How Sanctum Ties It Together

`bootstrap/app.php` adds Sanctum to the `api` middleware group:

```php
$middleware->api(prepend: [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
]);
```

This means a **browser (SPA)** that is already logged in via the web session can make calls to `/api/...` routes and be automatically authenticated — using the same cookie, no token needed. This is how frontend JavaScript on `/dashboard` can call `POST /api/ratings` and the server knows which user is logged in.
