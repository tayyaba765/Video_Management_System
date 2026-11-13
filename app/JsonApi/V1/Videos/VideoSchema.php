<?php 
namespace App\JsonApi\V1\Videos;

use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\ArrayList;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\Scope;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\Relations\HasManyThrough;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class VideoSchema extends Schema
{
    protected int $maxDepth = 2;

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    protected static ?string $model = Video::class;
// Disable schema-level authorization checks for public access
    protected bool $authorizable = false;

    /**
     * Build an index query for this resource with eager-loaded rating statistics.
     *
     * @param Request|null $request
     * @param Builder $query
     * @return Builder
     */
    public function indexQuery(?Request $request, Builder $query): Builder
    {
        return $query->withRatingsSummary();
    }

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('title')->sortable(),
            Str::make('youtubeId', 'youtube_id'),
            Str::make('category')->sortable(),
            Str::make('description'),
            Str::make('thumbnail'),
            Number::make('ratingsCount')->readOnly(),
            Number::make('avgScore')->readOnly(),
            ArrayList::make('topRatings')->readOnly(),
            Number::make('userRatingId')->readOnly(),
            Str::make('userRating')->readOnly(),
            Number::make('userScore')->readOnly(),
            Str::make('userReview')->readOnly(),
            HasMany::make('video-ratings', 'videoRatings')
                ->type('video-ratings')
                ->readOnly(),
            HasManyThrough::make('users')->type('users'),
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
            Where::make('title'),
            Where::make('category'),
            Scope::make('search'),
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