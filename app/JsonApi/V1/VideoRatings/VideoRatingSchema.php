<?php 
namespace App\JsonApi\V1\VideoRatings;

use App\Models\VideoRating;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Schema;

class VideoRatingSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     * @var string
     */
    protected static ?string $model = VideoRating::class;
// Disable schema-level authorization checks for public access
    protected bool $authorizable = false;
    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Number::make('score'),
            Str::make('rating'),
            Str::make('emoji')->readOnly(),
            Str::make('review'),
            BelongsTo::make('video')->type('videos'),
            BelongsTo::make('user')->type('users'),
            DateTime::make('createdAt', 'created_at')->readOnly()->sortable(),
            DateTime::make('updatedAt', 'updated_at')->readOnly(),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this)->delimiter(','),
            Where::make('rating'),
            Where::make('video', 'video_id'),
            Where::make('user', 'user_id'),
        ];
    }

    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
  
}