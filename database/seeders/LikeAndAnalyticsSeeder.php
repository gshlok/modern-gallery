<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Album;
use App\Models\Comment;
use App\Models\User;
use App\Models\Like;
use App\Models\AnalyticsEvent;
use Illuminate\Database\Seeder;

class LikeAndAnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $images = Image::where('visibility', 'public')->get();
        $albums = Album::where('visibility', 'public')->get();
        $comments = Comment::where('status', 'approved')->get();

        // Create likes for images
        foreach ($images as $image) {
            // Each image gets likes from 10-80% of users
            $likerCount = rand(
                (int)($users->count() * 0.1),
                (int)($users->count() * 0.8)
            );

            $likers = $users->where('id', '!=', $image->user_id)
                ->random($likerCount);

            foreach ($likers as $liker) {
                Like::create([
                    'user_id' => $liker->id,
                    'likeable_id' => $image->id,
                    'likeable_type' => Image::class,
                    'ip_address' => '127.0.0.1',
                    'created_at' => $image->created_at->addDays(rand(1, 60)),
                ]);
            }
        }

        // Create likes for albums
        foreach ($albums as $album) {
            $likerCount = rand(
                (int)($users->count() * 0.05),
                (int)($users->count() * 0.4)
            );

            $likers = $users->where('id', '!=', $album->user_id)
                ->random($likerCount);

            foreach ($likers as $liker) {
                Like::create([
                    'user_id' => $liker->id,
                    'likeable_id' => $album->id,
                    'likeable_type' => Album::class,
                    'ip_address' => '127.0.0.1',
                    'created_at' => $album->created_at->addDays(rand(1, 45)),
                ]);
            }
        }

        // Create likes for comments
        foreach ($comments->take(20) as $comment) {
            $likerCount = rand(0, min(5, $users->count()));

            if ($likerCount > 0) {
                $likers = $users->where('id', '!=', $comment->user_id)
                    ->random($likerCount);

                foreach ($likers as $liker) {
                    Like::create([
                        'user_id' => $liker->id,
                        'likeable_id' => $comment->id,
                        'likeable_type' => Comment::class,
                        'ip_address' => '127.0.0.1',
                        'created_at' => $comment->created_at->addHours(rand(1, 168)),
                    ]);
                }
            }
        }

        // Create analytics events
        $eventTypes = [
            'image_view',
            'image_download',
            'album_view',
            'search_query',
            'user_profile_view',
        ];

        $sessionIds = [];
        for ($i = 0; $i < 100; $i++) {
            $sessionIds[] = 'session_' . rand(100000, 999999);
        }

        // Generate analytics events for the past 90 days
        for ($day = 90; $day >= 0; $day--) {
            $date = now()->subDays($day);

            // Generate 50-200 events per day
            $eventsPerDay = rand(50, 200);

            for ($i = 0; $i < $eventsPerDay; $i++) {
                $eventType = $eventTypes[array_rand($eventTypes)];
                $user = rand(0, 3) ? $users->random() : null; // 75% chance of logged-in user
                $sessionId = $sessionIds[array_rand($sessionIds)];

                $trackableId = null;
                $trackableType = null;

                switch ($eventType) {
                    case 'image_view':
                        $image = $images->random();
                        $trackableId = $image->id;
                        $trackableType = Image::class;
                        break;
                    case 'image_download':
                        $image = $images->where('allow_download', true)->random();
                        if ($image) {
                            $trackableId = $image->id;
                            $trackableType = Image::class;
                        }
                        break;
                    case 'album_view':
                        $album = $albums->random();
                        $trackableId = $album->id;
                        $trackableType = Album::class;
                        break;
                    case 'search_query':
                    case 'user_profile_view':
                        // These don't have trackable entities for this demo
                        break;
                }

                if ($trackableId) {
                    AnalyticsEvent::create([
                        'event_type' => $eventType,
                        'trackable_id' => $trackableId,
                        'trackable_type' => $trackableType,
                        'user_id' => $user?->id,
                        'session_id' => $sessionId,
                        'ip_address' => '192.168.' . rand(1, 255) . '.' . rand(1, 255),
                        'user_agent' => 'Mozilla/5.0 (compatible; Gallery Platform)',
                        'metadata' => [
                            'referrer' => rand(0, 3) ? 'https://google.com' : null,
                            'device_type' => collect(['desktop', 'mobile', 'tablet'])->random(),
                        ],
                        'created_at' => $date->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                    ]);
                }
            }
        }

        $this->command->info('Created ' . Like::count() . ' likes');
        $this->command->info('Created ' . AnalyticsEvent::count() . ' analytics events');
    }
}
