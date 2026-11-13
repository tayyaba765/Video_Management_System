# Video Management System

A Laravel-based application for managing videos and allowing users to watch, score, rate, and comment on videos.

## Features

- Manage and store video information.
- Browse and watch videos.
- Give scores from 1–10.
- Rate videos from 1–5 stars.
- Add comments and reviews.
- View average scores and ratings.
- Store user feedback in the database.

## Technologies Used

- Laravel (PHP)
- MySQL
- HTML, CSS, JavaScript
- JSON:API

## Database Models

- User
- Video
- VideoRating
- <img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/d2672a52-c7ee-4fbd-ac34-ad5ccaafd1db" />
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/0aa88349-7e06-40df-a346-a5d00f0a1273" />
avergae score>6.5 api for dashboard---json api
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/35e360d8-9bac-4ae4-a800-44f080a40c40" />
average score and name wise
http://127.0.0.1:8000/api/v1/videos?filter[search]=incept&filter[avg-score-above]=5
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/9db37df3-a046-4d73-9669-0a5d5a789788" />
http://127.0.0.1:8000/api/v1/video-ratings?filter[rating]=love
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/88a43a2d-3973-4673-8e44-c19953cddbd9" />
/api/v1/video-ratings?filter[video]=12


for pivot tables
/api/v1/videos/{id}/users
/api/v1/videos/{id}/video-ratings
/api/v1/videos/{id}/relationships/video-ratings
/api/v1/users/{id}
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/ce8ebe65-1b52-48bf-bcc2-ab385158e05f" />
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/919ac3de-64a6-4c74-abe1-d3d37d75213c" />





