<?php
namespace App\JsonApi\V1\VideoRatings;

use App\Models\VideoRating;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Resources\JsonApiResource;

/**
 * @property VideoRating $resource
 */
class VideoRatingResource extends JsonApiResource
{
    /**
     * Get the resource's attributes.
     *
     * @param Request|null $request
     * @return iterable
     */
    public function attributes($request): iterable
    {
        return [
            'score'     => $this->resource->score,
            'rating'    => $this->resource->rating,
            'emoji'     => $this->resource->emoji,
            'review'    => $this->resource->review,
            'createdAt' => $this->resource->created_at,
            'updatedAt' => $this->resource->updated_at,
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
            'video' => $this->relation('video')->showDataIfLoaded(),
            'user'  => $this->relation('user')->showDataIfLoaded(),
        ];
    }
}