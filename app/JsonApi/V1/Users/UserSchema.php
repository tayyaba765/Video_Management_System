<?php
namespace App\JsonApi\V1\Users;

use App\Models\User;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\Relations\HasMany;
use LaravelJsonApi\Eloquent\Fields\Relations\HasManyThrough;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class UserSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    protected static ?string $model = User::class;
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
            Str::make('name')->sortable(),
            Str::make('email')->sortable(),
            HasMany::make('video-ratings', 'videoRatings')
                ->type('video-ratings')
                ->readOnly(),
            HasManyThrough::make('videos')->type('videos'),
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