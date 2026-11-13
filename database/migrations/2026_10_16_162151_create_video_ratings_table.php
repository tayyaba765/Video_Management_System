<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('video_ratings', function (Blueprint $table) {
    $table->id();

    // Video being rated
    $table->foreignId('video_id')
        ->constrained('videos')
        ->cascadeOnDelete();

    // User who submitted the rating
    $table->foreignId('user_id')
        ->constrained('users')
        ->cascadeOnDelete();
$table->unique(['user_id', 'video_id']);
    $table->enum('rating', [
        'dislike',
        'hate',
        'satisfied',
        'neutral',
        'dissatisfied',
        'love',
        'like',
    ]);

    $table->unsignedTinyInteger('score');

    $table->text('review')->nullable();



    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('video_ratings');
    }
};