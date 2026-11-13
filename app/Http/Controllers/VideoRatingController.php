<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoRating;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class VideoRatingController extends Controller
{
    public const RATINGS = [
        'hate',
        'dislike',
        'dissatisfied',
        'neutral',
        'satisfied',
        'like',
        'love',
    ];

    public function index()
    {
        return view('video-ratings.index');
    }

    public function videos(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // UPDATED: capture user id once and use it to eager-load their rating
        $userId = Auth::id();

        $videos = Video::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      // UPDATED: also search category so placeholder matches reality
                      ->orWhere('category', 'like', "%{$q}%");
                });
            })
            ->withCount('ratings')
            ->withAvg('ratings', 'score')
            ->withCount([
                'ratings as hate_count'         => fn ($q) => $q->where('rating', 'hate'),
                'ratings as dislike_count'      => fn ($q) => $q->where('rating', 'dislike'),
                'ratings as dissatisfied_count' => fn ($q) => $q->where('rating', 'dissatisfied'),
                'ratings as neutral_count'      => fn ($q) => $q->where('rating', 'neutral'),
                'ratings as satisfied_count'    => fn ($q) => $q->where('rating', 'satisfied'),
                'ratings as like_count'         => fn ($q) => $q->where('rating', 'like'),
                'ratings as love_count'         => fn ($q) => $q->where('rating', 'love'),
            ])
            // UPDATED: eager-load the current user's own rating (0 or 1 per video).
            // This is what kills the frontend N+1 — no more /api/videos/{id}/ratings calls.
            ->with(['ratings' => function ($query) use ($userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }])
            ->latest()
            ->get()
            ->map(function (Video $video) {
                $ratingCounts = [
                    'hate'         => $video->hate_count,
                    'dislike'      => $video->dislike_count,
                    'dissatisfied' => $video->dissatisfied_count,
                    'neutral'      => $video->neutral_count,
                    'satisfied'    => $video->satisfied_count,
                    'like'         => $video->like_count,
                    'love'         => $video->love_count,
                ];

                $ratingEmojis = [
                    'hate' => '😡', 'dislike' => '😞', 'dissatisfied' => '🙁',
                    'neutral' => '😐', 'satisfied' => '🙂', 'like' => '👍', 'love' => '❤️',
                ];

                arsort($ratingCounts);

                $topRatings = [];
                foreach ($ratingCounts as $rating => $count) {
                    if ($count > 0) {
                        $topRatings[] = [
                            'rating' => $rating,
                            'emoji'  => $ratingEmojis[$rating],
                            'count'  => $count,
                        ];
                    }
                    if (count($topRatings) === 3) break;
                }

                $userRating = $video->ratings->first();

                return [
                    'id'     => $video->id,
                    'title'  => $video->title,
                    'category'  => $video->category ?? null,
                    'year'   => $video->year ?? null,

                    // UPDATED: the frontend was already trying to read these.
                    // They now exist.
                    'description' => $video->description ?? null,
                    'youtube_id'  => $video->youtube_id ?? null,

                    // UPDATED: support both stored relative paths and full URLs
          'thumbnail' => !empty($video->thumbnail)
    ? (str_starts_with($video->thumbnail, 'http')
        ? $video->thumbnail
        : 'http://192.168.1.38:8000/storage/' . ltrim($video->thumbnail, '/'))
    : null,

                    'ratings_count' => $video->ratings_count,
                    'avg_score'     => $video->ratings_avg_score !== null
                        ? round($video->ratings_avg_score, 1)
                        : null,
                    'top_ratings'   => $topRatings,

                    // UPDATED: current user's own rating info — returned in the
                    // same response so the frontend needs zero extra calls.
                    'user_rating_id' => $userRating?->id,
                    'user_rating'    => $userRating?->rating,
                    'user_score'     => $userRating?->score,
                    'user_review'    => $userRating?->review,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $videos,
        ]);
    }

    public function ratings(Video $video)
    {
        $ratings = $video->ratings()
            ->with('user')
            ->latest()
            ->get()
            ->map(fn (VideoRating $rating) => [
                'id'         => $rating->id,
                'video_id'   => $rating->video_id,
                'user_id'    => $rating->user_id,
                'user'       => $rating->user ? [
                    'id'   => $rating->user->id,
                    'name' => $rating->user->name,
                ] : null,
                'rating'     => $rating->rating,
                'score'      => $rating->score,
                'review'     => $rating->review,
                'created_at' => $rating->created_at,
            ]);

        return response()->json([
            'success' => true,
            'video'   => [
                'id'    => $video->id,
                'title' => $video->title,
            ],
            'ratings' => $ratings,
        ]);
    }

    public function store(Request $request)
    {
        // UPDATED: explicit auth check (routes should have middleware too,
        // but this makes the controller self-defending).
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to rate.',
            ], 401);
        }

        $data = $request->validate([
            'video_id' => ['required', 'exists:videos,id'],
            'rating'   => ['required', Rule::in(self::RATINGS)],
            'score'    => ['required', 'integer', 'between:1,10'],
            'review'   => ['nullable', 'string', 'max:2000'],
        ]);

        // UPDATED: prevent duplicate ratings (belt + braces with the DB unique index).
        $existing = VideoRating::where('user_id', Auth::id())
            ->where('video_id', $data['video_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success'   => false,
                'message'   => 'You have already rated this video.',
                'rating_id' => $existing->id,
            ], 409);
        }

        $data['user_id'] = Auth::id();
        $rating = VideoRating::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Rating saved successfully!',
            'data'    => $rating->load('user'),
        ], 201);
    }

    public function update(Request $request, VideoRating $rating)
    {
        if (!Auth::check() || $rating->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update your own rating.',
            ], 403);
        }

        $data = $request->validate([
            'rating' => ['required', Rule::in(self::RATINGS)],
            'score'  => ['required', 'integer', 'between:1,10'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $rating->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Rating updated successfully!',
            'data'    => $rating->fresh()->load('user'),
        ]);
    }

    public function destroy(VideoRating $rating)
    {
        if (!Auth::check() || $rating->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own rating.',
            ], 403);
        }

        $rating->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rating deleted successfully!',
        ]);
    }
}