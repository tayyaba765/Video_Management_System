<?php

namespace App\JsonApi\V1;

use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{
    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/api/v1';
  public function authorizable(): bool
    {
        return false;
    }
    /**
     * Get the server's middleware.
     *
     * @return array
     */
    public function middleware(): array
    {
        return []; // Returning an empty array removes mandatory authentication for this server
    }

    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void
    {
        // no-op
    }

    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    
    protected function allSchemas(): array
    {
        return [
            Videos\VideoSchema::class,
            VideoRatings\VideoRatingSchema::class,
            Users\UserSchema::class,
        ];
    }
}