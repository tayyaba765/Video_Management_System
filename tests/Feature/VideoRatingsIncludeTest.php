<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use App\Models\VideoRating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoRatingsIncludeTest extends TestCase
{
    use RefreshDatabase;

    public function test_video_ratings_are_included_with_a_sparse_video_fieldset(): void
    {
        $user = User::factory()->create();
        $video = Video::create([
            'title' => 'Example video',
            'youtube_id' => 'example-video',
        ]);

        VideoRating::create([
            'video_id' => $video->id,
            'user_id' => $user->id,
            'score' => 5,
            'rating' => 'like',
            'review' => 'Great video',
        ]);

        $this->get('/api/v1/videos?fields%5Bvideos%5D=title&include=video-ratings', [
            'Accept' => 'application/vnd.api+json',
        ])
            ->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'Example video')
            ->assertJsonMissingPath('data.0.attributes.category')
            ->assertJsonPath('included.0.type', 'video-ratings')
            ->assertJsonPath('included.0.attributes.score', 5)
            ->assertJsonPath('included.0.attributes.review', 'Great video');
    }

    public function test_video_ratings_summary_attributes_are_returned(): void
    {
        $user = User::factory()->create();
        $video = Video::create([
            'title' => 'Summary video',
            'youtube_id' => 'summary-video',
            'thumbnail' => 'thumbnails/test.jpg',
        ]);

        VideoRating::create([
            'video_id' => $video->id,
            'user_id' => $user->id,
            'score' => 8,
            'rating' => 'love',
            'review' => 'Incredible',
        ]);

        $this->actingAs($user)
            ->get('/api/v1/videos', [
                'Accept' => 'application/vnd.api+json',
            ])
            ->assertOk()
            ->assertJsonPath('data.0.attributes.ratingsCount', 1)
            ->assertJsonPath('data.0.attributes.avgScore', 8)
            ->assertJsonPath('data.0.attributes.topRatings.0.rating', 'love')
            ->assertJsonPath('data.0.attributes.topRatings.0.emoji', '❤️')
            ->assertJsonPath('data.0.attributes.topRatings.0.count', 1)
            ->assertJsonPath('data.0.attributes.userRating', 'love')
            ->assertJsonPath('data.0.attributes.userScore', 8);

        $this->get('/api/v1/video-ratings', [
            'Accept' => 'application/vnd.api+json',
        ])
            ->assertOk()
            ->assertJsonPath('data.0.attributes.emoji', '❤️');
    }
}
