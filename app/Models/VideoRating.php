<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Relations\HasMany;
class VideoRating extends Model
{
    protected $fillable = [
        'video_id',
        'user_id',
        'score',
        'rating',
        'review',
    
      
    ];

    public const EMOJIS = [
        'hate'         => '😡',
        'dislike'      => '😞',
        'dissatisfied' => '🙁',
        'neutral'      => '😐',
        'satisfied'    => '🙂',
        'like'         => '👍',
        'love'         => '❤️',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function getEmojiAttribute(): ?string
    {
        return self::EMOJIS[$this->rating] ?? null;
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}