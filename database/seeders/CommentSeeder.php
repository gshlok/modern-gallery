<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $images = Image::where('visibility', 'public')->get();

        $sampleComments = [
            'Absolutely stunning composition! The lighting is perfect.',
            'This captures the mood beautifully. Great work!',
            'Love the perspective you chose for this shot.',
            'The colors in this image are incredible.',
            'Such a peaceful and serene moment captured.',
            'Amazing detail in this photograph!',
            'This tells a powerful story. Well done.',
            'The technical execution here is flawless.',
            'Beautiful work! What camera settings did you use?',
            'This is photography at its finest.',
            'The emotion in this image is palpable.',
            'Fantastic use of natural light.',
            'This composition is so well balanced.',
            'The clarity and sharpness are impressive.',
            'You have a great eye for capturing the moment.',
            'This shot has such great energy.',
            'The textures in this image are amazing.',
            'Perfect timing on this capture!',
            'This is going straight to my inspiration folder.',
            'The depth of field creates such nice separation.',
        ];

        $replies = [
            'Thank you so much for the kind words!',
            'I really appreciate your feedback.',
            'Thanks! I spent quite some time getting this shot right.',
            'Your support means a lot to me.',
            'Glad you enjoyed it!',
            'Thank you! It was shot with natural light only.',
            'I\'m thrilled you like the composition.',
            'Thanks for taking the time to comment!',
        ];

        // Create top-level comments
        foreach ($images->take(30) as $image) {
            // 1-5 comments per image
            $commentCount = rand(1, 5);

            for ($i = 0; $i < $commentCount; $i++) {
                $commenter = $users->where('id', '!=', $image->user_id)->random();

                $comment = Comment::create([
                    'image_id' => $image->id,
                    'user_id' => $commenter->id,
                    'content' => $sampleComments[array_rand($sampleComments)],
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => 1, // Admin user
                    'ip_address' => '127.0.0.1',
                    'created_at' => $image->created_at->addDays(rand(1, 30)),
                ]);

                // 30% chance of a reply from the image owner
                if (rand(1, 10) <= 3) {
                    Comment::create([
                        'image_id' => $image->id,
                        'user_id' => $image->user_id,
                        'parent_id' => $comment->id,
                        'content' => $replies[array_rand($replies)],
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => 1,
                        'ip_address' => '127.0.0.1',
                        'created_at' => $comment->created_at->addHours(rand(1, 24)),
                    ]);
                }
            }
        }

        // Create some pending comments for moderation testing
        foreach ($images->take(5) as $image) {
            $commenter = $users->random();

            Comment::create([
                'image_id' => $image->id,
                'user_id' => $commenter->id,
                'content' => 'This is a pending comment waiting for moderation approval.',
                'status' => 'pending',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(rand(1, 48)),
            ]);
        }

        $this->command->info('Created ' . Comment::count() . ' comments');
    }
}
