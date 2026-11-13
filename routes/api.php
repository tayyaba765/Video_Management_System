<?php

use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
use App\Http\Middleware\IncludeRequestedRelationshipsInSparseFieldsets;

JsonApiRoute::server('v1')
    ->prefix('v1')
    ->middleware(IncludeRequestedRelationshipsInSparseFieldsets::class)
    ->resources(function (ResourceRegistrar $server) {
        $server->resource('videos', JsonApiController::class)
            ->relationships(function ($relationships) {
                $relationships->hasMany('video-ratings')->readOnly();
                $relationships->hasMany('users')->readOnly();
            });

        $server->resource('video-ratings', JsonApiController::class)
            ->relationships(function ($relationships) {
                $relationships->hasOne('video');
                $relationships->hasOne('user');
            });

        $server->resource('users', JsonApiController::class)
            ->relationships(function ($relationships) {
                $relationships->hasMany('video-ratings')->readOnly();
                $relationships->hasMany('videos')->readOnly();
            });
    });