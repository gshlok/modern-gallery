<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlbumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $albums = [
            [
                'title' => 'Mystic Mountains',
                'description' => 'A collection of breathtaking mountain landscapes captured during a month-long expedition through the Himalayas.',
                'visibility' => 'public',
                'is_featured' => true,
            ],
            [
                'title' => 'Urban Stories',
                'description' => 'Street photography documenting the daily life and culture of major metropolitan cities.',
                'visibility' => 'public',
                'is_featured' => true,
            ],
            [
                'title' => 'Portrait Sessions 2024',
                'description' => 'Professional portrait work featuring diverse subjects and creative lighting techniques.',
                'visibility' => 'public',
                'is_featured' => false,
            ],
            [
                'title' => 'Architectural Wonders',
                'description' => 'Modern and classical architecture showcasing the evolution of design and construction.',
                'visibility' => 'public',
                'is_featured' => true,
            ],
            [
                'title' => 'Nature\'s Patterns',
                'description' => 'Macro photography revealing the intricate patterns and textures found in nature.',
                'visibility' => 'public',
                'is_featured' => false,
            ],
            [
                'title' => 'Golden Hour Collection',
                'description' => 'Images captured during the magical golden hour, showcasing warm light and dramatic shadows.',
                'visibility' => 'public',
                'is_featured' => true,
            ],
            [
                'title' => 'Black & White Classics',
                'description' => 'Timeless monochromatic photography emphasizing composition, contrast, and emotion.',
                'visibility' => 'public',
                'is_featured' => false,
            ],
            [
                'title' => 'Wildlife Safari',
                'description' => 'Incredible wildlife encounters captured during African safari expeditions.',
                'visibility' => 'public',
                'is_featured' => true,
            ],
            [
                'title' => 'Abstract Expressions',
                'description' => 'Experimental photography exploring color, form, and movement in abstract compositions.',
                'visibility' => 'public',
                'is_featured' => false,
            ],
            [
                'title' => 'Travel Memories',
                'description' => 'A personal collection of travel photography from around the world.',
                'visibility' => 'unlisted',
                'is_featured' => false,
            ],
            [
                'title' => 'Client Work Archive',
                'description' => 'Private collection of professional client work and commercial photography.',
                'visibility' => 'private',
                'is_featured' => false,
            ],
            [
                'title' => 'Experimental Series',
                'description' => 'Work-in-progress experimental photography testing new techniques and concepts.',
                'visibility' => 'private',
                'is_featured' => false,
            ],
        ];

        foreach ($albums as $index => $albumData) {
            // Assign albums to different users
            $user = $users->skip($index % $users->count())->first();

            Album::create([
                'user_id' => $user->id,
                'title' => $albumData['title'],
                'description' => $albumData['description'],
                'visibility' => $albumData['visibility'],
                'is_featured' => $albumData['is_featured'],
                'sort_order' => $index,
                'settings' => [
                    'theme' => 'default',
                    'layout' => 'grid',
                    'show_metadata' => true,
                ],
            ]);
        }

        $this->command->info('Created ' . count($albums) . ' albums');
    }
}
