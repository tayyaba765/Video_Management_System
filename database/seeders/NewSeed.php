<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Video;

class NewSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => 'Tayyaba',
            'email' => 'tayyaba@example.com',
            'password' => 'password',
        ]);

        User::create([
            'name' => 'Ali',
            'email' => 'ali@example.com',
            'password' => 'password',
        ]);

        User::create([
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => 'password',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Videos
        |--------------------------------------------------------------------------
        */

        Video::create([
            'title' => 'Inception',
            'youtube_id' => 'YoHD_xQCoaY',
            'category' => 'Science Fiction',
            'description' => 'A thief who steals secrets through dreams is given a chance to erase his past.',
        ]);

        Video::create([
            'title' => 'The Dark Knight',
            'youtube_id' => 'EXeTwQWrcwY',
            'category' => 'Action',
            'description' => 'Batman faces the Joker, a criminal mastermind who wants to create chaos in Gotham.',
        ]);

        Video::create([
            'title' => 'Interstellar',
            'youtube_id' => 'zSWdZVtXT7E',
            'category' => 'Science Fiction',
            'description' => 'A group of astronauts travels through space to find a new home for humanity.',
        ]);

        Video::create([
            'title' => 'Avengers Endgame',
            'youtube_id' => 'TcMBFSGVi1c',
            'category' => 'Action',
            'description' => 'The Avengers attempt to undo the damage caused by Thanos.',
        ]);

        Video::create([
            'title' => 'The Matrix',
            'youtube_id' => 'vKQi3bBA1y8',
            'category' => 'Science Fiction',
            'description' => 'A computer hacker discovers that the world he knows is actually a simulation.',
        ]);

        Video::create([
            'title' => 'Titanic',
            'youtube_id' => 'kVrqfYjkTdQ',
            'category' => 'Romance',
            'description' => 'A young couple falls in love aboard the famous Titanic.',
        ]);

        Video::create([
            'title' => 'Gladiator',
            'youtube_id' => 'P5ieIbInFpg',
            'category' => 'Action',
            'description' => 'A Roman general becomes a gladiator and seeks revenge.',
        ]);

        Video::create([
            'title' => 'The Lion King',
            'youtube_id' => '7TavVZMewpY',
            'category' => 'Animation',
            'description' => 'A young lion must find his place in the circle of life.',
        ]);
    }
}

