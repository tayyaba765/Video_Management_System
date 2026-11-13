<!DOCTYPE html>
<html lang="en">
<!--
    
App\Models\Video::query()->each(function ($v) {
    if ($v->thumbnail && str_contains($v->thumbnail, '127.0.0.1')) {
        $v->thumbnail = str_replace('127.0.0.1', '192.168.1.38', $v->thumbnail);
        $v->save();
    }
});
-->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Ratings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Videos</h2>
            <form method="POST" action="/logout" style="margin:0">
                @csrf
                <button type="submit" id="logoutButton" class="btn btn-danger">Logout</button>
            </form>
        </div>

        <div class="mb-4">
            <input type="text" id="search" class="form-control"
                placeholder="Search video by title or category...">
        </div>

        <div class="row" id="videoGrid">
            <div class="col-12">
                <div class="alert alert-info">Loading videos...</div>
            </div>
        </div>
    </div>

    {{-- UPDATED: no `new bootstrap.Modal()` calls, no dead code blocks --}}

    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ratingModalLabel">Rate Video</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ratingForm">
                        <input type="hidden" id="video_id">

                        <div class="mb-4">
                            <label class="form-label">How did you feel about this video?</label>
                            <div class="d-flex flex-wrap gap-2" id="ratingButtons">
                                {{-- UPDATED: hate is now dark, not red, so it's visually distinct from love --}}
                                <button type="button" class="btn btn-outline-dark rating-btn" data-rating="hate">😡
                                    Hate</button>
                                <button type="button" class="btn btn-outline-warning rating-btn"
                                    data-rating="dislike">😞 Dislike</button>
                                <button type="button" class="btn btn-outline-warning rating-btn"
                                    data-rating="dissatisfied">🙁 Dissatisfied</button>
                                <button type="button" class="btn btn-outline-secondary rating-btn"
                                    data-rating="neutral">😐 Neutral</button>
                                <button type="button" class="btn btn-outline-info rating-btn"
                                    data-rating="satisfied">🙂 Satisfied</button>
                                <button type="button" class="btn btn-outline-primary rating-btn" data-rating="like">👍
                                    Like</button>
                                <button type="button" class="btn btn-outline-danger rating-btn" data-rating="love">❤️
                                    Love</button>
                            </div>
                            <input type="hidden" id="rating">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Score: <strong><span id="scoreValue">5</span>/10</strong>
                            </label>
                            <input type="range" id="score" class="form-range" min="1" max="10"
                                value="5" step="1">
                            <div class="progress">
                                <div id="scoreProgress" class="progress-bar" role="progressbar" style="width:50%;">5 /
                                    10</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="review" class="form-label">Review</label>
                            <textarea id="review" class="form-control" rows="4" placeholder="Write your review..."></textarea>
                        </div>

                        {{-- UPDATED: inline error box instead of alert() --}}
                        <div id="formError" class="alert alert-danger d-none"></div>

                        <button type="submit" id="submitBtn" class="btn btn-primary w-100">Submit Rating</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const currentUserId = {{ auth()->id() ?? 'null' }};
        let selectedVideoId = null;
        let existingRatingId = null;
        let allVideos = [];
        const VIDEO_API = '/api/videos';

        // UPDATED: HTML-escape helper — prevents XSS from titles, descriptions, reviews
        function esc(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        // UPDATED: abort previous in-flight search when a new one starts
        let searchTimer = null;
        let searchAbort = null;

        function getVideos(search = '') {
            if (searchAbort) searchAbort.abort();
            searchAbort = new AbortController();

            let url = VIDEO_API;
            if (search !== '') url += '?q=' + encodeURIComponent(search);

            fetch(url, {
                    signal: searchAbort.signal
                })
                .then(res => {
                    if (!res.ok) throw new Error('Unable to fetch videos.');
                    return res.json();
                })
                .then(result => {
                    if (!result.success) throw new Error(result.message || 'Unable to load videos.');
                    allVideos = result.data || [];
                    showVideos(allVideos);
                })
                .catch(err => {
                    if (err.name === 'AbortError') return;
                    document.getElementById('videoGrid').innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <strong>Unable to load videos.</strong><br>${esc(err.message)}
                            </div>
                        </div>`;
                });
        }

        const ratingEmojis = {
            'hate': '😡',
            'dislike': '😞',
            'dissatisfied': '🙁',
            'neutral': '😐',
            'satisfied': '🙂',
            'like': '👍',
            'love': '❤️',
        };

        function showVideos(videos) {
            const grid = document.getElementById('videoGrid');
            grid.innerHTML = '';

            if (!videos || videos.length === 0) {
                grid.innerHTML = `<div class="col-12"><div class="alert alert-info">No videos found.</div></div>`;
                return;
            }

            // UPDATED: build HTML once instead of `grid.innerHTML +=` per iteration
            let html = '';

            videos.forEach(video => {

                const backendUrl = 'http://192.168.1.38:8000';

                const thumbnail = video.thumbnail && video.thumbnail !== '' ?
                    (video.thumbnail.startsWith('http') ?
                        video.thumbnail :
                        backendUrl + (video.thumbnail.startsWith('/') ? '' : '/') + video.thumbnail) :
                    'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=800&q=80';

                let topRatingsHtml;
                if (video.top_ratings && video.top_ratings.length > 0) {
                    topRatingsHtml = video.top_ratings.map(item =>
                        `<span class="me-2">${esc(item.emoji)} ${esc(item.count)}</span>`
                    ).join('');
                } else {
                    topRatingsHtml = `<span class="text-muted">No ratings yet</span>`;
                }

                // UPDATED: read user_rating_* from the payload — no per-card fetch
                const userRatingId = video.user_rating_id ?? video.userRatingId ?? null;
                const userRating = video.user_rating ?? video.userRating ?? null;
                const userScore = video.user_score ?? video.userScore ?? null;
                const userReview = video.user_review ?? video.userReview ?? null;

                const hasRated = userRatingId != null || userScore != null;
                const btnClass = hasRated ? 'btn-success' : 'btn-primary';
                const btnLabel = hasRated ? '✏️ Re-rate This Video' : 'Rate This Video';

                html += `
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <img src="${esc(thumbnail)}" alt="${esc(video.title || 'Video')}"
                                 class="card-img-top" style="height:220px;object-fit:cover;">
                            <div class="card-body">
                                <h5 class="card-title">${esc(video.title || 'Untitled Video')}</h5>

                                {{-- UPDATED: use category (backend field), not category --}}
                                <p class="text-muted mb-1"><strong>category:</strong> ${esc(video.category || 'N/A')}</p>

                                <p class="card-text small">${esc(video.description || 'No description available.')}</p>

                                <p class="small text-muted">
                                    <strong>YouTube ID:</strong> ${esc(video.youtube_id || 'N/A')}
                                </p>

                                <div class="mb-3">
                                    <span class="badge bg-secondary">Ratings: ${esc(video.ratings_count ?? video.ratingsCount ?? 0)}</span>
                                    <span class="badge bg-warning text-dark">
                                        Average Score: ${video.avg_score != null ? esc(video.avg_score) : (video.avgScore != null ? esc(video.avgScore) : 'No ratings')}
                                    </span>
                                </div>

                                <div class="mb-2">${topRatingsHtml}</div>

                                ${hasRated ? `
                                    <div class="card bg-light border border-success p-2 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small fw-bold text-success">
                                                Your Rating: ${ratingEmojis[userRating] || '⭐'} <span class="text-capitalize">${esc(userRating || '')}</span>
                                            </span>
                                            <span class="badge bg-success">${esc(userScore)} / 10</span>
                                        </div>
                                        ${userReview ? `<div class="small text-muted fst-italic">"${esc(userReview)}"</div>` : '<div class="small text-muted fst-italic">No review written.</div>'}
                                    </div>
                                ` : ''}

                                <button type="button"
                                        class="btn ${btnClass} w-100"
                                        id="rateButton-${esc(video.id)}"
                                        onclick="openRatingModal(${Number(video.id)}, this)">
                                    ${btnLabel}
                                </button>
                            </div>
                        </div>
                    </div>`;
            });

            grid.innerHTML = html;
        }

        function updateScoreDisplay() {
            const input = document.getElementById('score');
            const val = input.value;
            document.getElementById('scoreValue').textContent = val;
            const bar = document.getElementById('scoreProgress');
            bar.style.width = (val / 10) * 100 + '%';
            bar.textContent = val + ' / 10';
        }

        document.getElementById('score').addEventListener('input', updateScoreDisplay);

        document.querySelectorAll('.rating-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('rating').value = this.dataset.rating;
                document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // UPDATED: no fetch here — we already have the user's rating in `allVideos`
        function openRatingModal(videoId, button) {
            selectedVideoId = videoId;

            const video = allVideos.find(v => Number(v.id) === Number(videoId));
            if (!video) return;

            const userRatingId = video.user_rating_id ?? video.userRatingId ?? null;
            const userRating = video.user_rating ?? video.userRating ?? null;
            const userScore = video.user_score ?? video.userScore ?? null;
            const userReview = video.user_review ?? video.userReview ?? null;

            existingRatingId = userRatingId;

            const ratingField = document.getElementById('rating');
            const scoreField = document.getElementById('score');
            const reviewField = document.getElementById('review');

            // Reset first, then restore if rated
            document.getElementById('ratingForm').reset();
            document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
            ratingField.value = '';
            scoreField.value = 5;
            reviewField.value = '';
            document.getElementById('formError').classList.add('d-none');

            if (existingRatingId) {
                ratingField.value = userRating;
                scoreField.value = userScore ?? 5;
                reviewField.value = userReview ?? '';

                document.querySelectorAll('.rating-btn').forEach(b => {
                    if (b.dataset.rating === userRating) b.classList.add('active');
                });
            }

            updateScoreDisplay();
            document.getElementById('video_id').value = videoId;

            // UPDATED: getOrCreateInstance — no stacked modals or stuck backdrops
            bootstrap.Modal.getOrCreateInstance(
                document.getElementById('ratingModal')
            ).show();
        }

        document.getElementById('ratingForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const errorBox = document.getElementById('formError');
            errorBox.classList.add('d-none');

            if (!selectedVideoId) {
                errorBox.textContent = 'Please select a video first.';
                errorBox.classList.remove('d-none');
                return;
            }

            const rating = document.getElementById('rating').value.trim();
            const score = document.getElementById('score').value;
            const review = document.getElementById('review').value.trim();

            // UPDATED: client-side validation — hidden inputs ignore `required`
            if (!rating) {
                errorBox.textContent = 'Please pick a rating emoji.';
                errorBox.classList.remove('d-none');
                return;
            }

            const formData = new FormData();
            formData.append('video_id', selectedVideoId);
            formData.append('rating', rating);
            formData.append('score', score);
            formData.append('review', review);

            let url = '/api/ratings';
            if (existingRatingId) {
                url = '/api/ratings/' + existingRatingId;
                formData.append('_method', 'PUT');
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving…';

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json().then(data => ({
                    ok: res.ok,
                    data
                })))
                .then(({
                    ok,
                    data
                }) => {
                    if (ok && data.success) {
                        // UPDATED: no alert(); just close and refresh
                        bootstrap.Modal.getInstance(
                            document.getElementById('ratingModal')
                        )?.hide();

                        getVideos(document.getElementById('search').value.trim());
                    } else {
                        // UPDATED: show Laravel validation errors if present
                        errorBox.textContent = data.errors ?
                            Object.values(data.errors).flat().join(' ') :
                            (data.message || 'Something went wrong.');
                        errorBox.classList.remove('d-none');
                    }
                })
                .catch(() => {
                    errorBox.textContent = 'Unable to submit rating.';
                    errorBox.classList.remove('d-none');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Rating';
                });
        });

        // UPDATED: debounce + `input` event (not `keyup`)
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            const value = this.value.trim();
            searchTimer = setTimeout(() => getVideos(value), 300);
        });

        getVideos();
    </script>

</body>

</html>
