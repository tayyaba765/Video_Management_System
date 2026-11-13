<?php
namespace App\JsonApi\V1\Videos;

use App\Models\Video;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Resources\JsonApiResource;

/**
 * @property Video $resource
 */
class VideoResource extends JsonApiResource
{
    /**
     * Get the resource's attributes.
     *
     * @param Request|null $request
     * @return iterable
     */
    public function attributes($request): iterable
    {
        $userRating = $this->resource->currentUserRating();

        $avgScore = $this->resource->ratings_avg_score !== null
            ? round((float) $this->resource->ratings_avg_score, 1)
            : ($this->resource->ratings()->avg('score') !== null
                ? round((float) $this->resource->ratings()->avg('score'), 1)
                : null);

        $ratingsCount = $this->resource->ratings_count
            ?? $this->resource->ratings()->count();

        return [
            'title'        => $this->resource->title,
            'youtubeId'    => $this->resource->youtube_id,
            'category'     => $this->resource->category,
            'description'  => $this->resource->description,
            'thumbnail'    => $this->resource->thumbnail_url,
            'ratingsCount' => (int) $ratingsCount,
            'avgScore'     => $avgScore,
            'topRatings'   => $this->resource->top_ratings,
            'userRatingId' => $userRating?->id,
            'userRating'   => $userRating?->rating,
            'userScore'    => $userRating?->score,
            'userReview'   => $userRating?->review,
            'createdAt'    => $this->resource->created_at,
            'updatedAt'    => $this->resource->updated_at,
        ];
    }

    /**
     * Get the resource's relationships.
     *
     * @param Request|null $request
     * @return iterable
     */
 public function relationships($request): iterable
    {
        return [
            'video-ratings' => $this->relation('video-ratings')->showDataIfLoaded(),
            'users' => $this->relation('users')->showDataIfLoaded(),
        ];
    }
}