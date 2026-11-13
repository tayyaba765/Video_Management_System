<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Auth;

class Video extends Model
{
    protected $fillable = [
        'title',
        'youtube_id',
        'category',
        'description',
        'thumbnail',
    ];

    public function videoRatings(): HasMany
    {
        return $this->hasMany(VideoRating::class, 'video_id');
    }

    /**
     * Alias for videoRatings() to maintain backwards compatibility with existing controllers.
     */
    public function ratings(): HasMany
    {
        return $this->videoRatings();
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            VideoRating::class,
            'video_id',
            'id',
            'id',
            'user_id',
        );
    }

    /**
     * Scope: full-text LIKE search across title, category, description.
     * Used by JSON:API ?filter[search]=term
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%' . $term . '%';
        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
              ->orWhere('category', 'like', $like)
              ->orWhere('description', 'like', $like);
        });
    }

    /**
     * Scope: eager-load ratings counts, average score, per-reaction counts,
     * and current user rating to eliminate N+1 queries.
     */
    public function scopeWithRatingsSummary(Builder $query, ?int $userId = null): Builder
    {
        $userId ??= Auth::id();

        return $query
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
            ->with(['ratings' => function ($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            }]);
    }

    /**
     * Format thumbnail as full URL or return null.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (empty($this->thumbnail)) {
            return null;
        }

        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        return url('/storage/' . ltrim($this->thumbnail, '/'));
    }

    /**
     * Get top 3 reactions with emoji and count.
     */
    public function getTopRatingsAttribute(): array
    {
        $ratingCounts = [
            'hate'         => $this->hate_count ?? $this->ratings()->where('rating', 'hate')->count(),
            'dislike'      => $this->dislike_count ?? $this->ratings()->where('rating', 'dislike')->count(),
            'dissatisfied' => $this->dissatisfied_count ?? $this->ratings()->where('rating', 'dissatisfied')->count(),
            'neutral'      => $this->neutral_count ?? $this->ratings()->where('rating', 'neutral')->count(),
            'satisfied'    => $this->satisfied_count ?? $this->ratings()->where('rating', 'satisfied')->count(),
            'like'         => $this->like_count ?? $this->ratings()->where('rating', 'like')->count(),
            'love'         => $this->love_count ?? $this->ratings()->where('rating', 'love')->count(),
        ];

        $ratingEmojis = [
            'hate'         => '😡',
            'dislike'      => '😞',
            'dissatisfied' => '🙁',
            'neutral'      => '😐',
            'satisfied'    => '🙂',
            'like'         => '👍',
            'love'         => '❤️',
        ];

        arsort($ratingCounts);

        $topRatings = [];
        foreach ($ratingCounts as $rating => $count) {
            if ($count > 0) {
                $topRatings[] = [
                    'rating' => $rating,
                    'emoji'  => $ratingEmojis[$rating] ?? '⭐',
                    'count'  => $count,
                ];
            }
            if (count($topRatings) === 3) {
                break;
            }
        }

        return $topRatings;
    }

    /**
     * Get current user's rating on this video.
     */
    public function currentUserRating(): ?VideoRating
    {
        $userId = Auth::id();
        if (!$userId) {
            return null;
        }

        if ($this->relationLoaded('ratings')) {
            return $this->ratings->firstWhere('user_id', $userId);
        }

        return $this->ratings()->where('user_id', $userId)->first();
    }
}