<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Album;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $albums = Album::all();
        $tags = Tag::all();

        // Sample image data with realistic photography metadata
        $sampleImages = [
            [
                'title' => 'Misty Mountain Peak',
                'description' => 'Early morning mist rolling over a dramatic mountain peak, captured during a hiking expedition.',
                'alt_text' => 'Mountain peak shrouded in morning mist',
                'width' => 1920,
                'height' => 1280,
                'visibility' => 'public',
                'is_featured' => true,
                'tags' => ['Nature', 'Mountain', 'Landscape'],
                'exif_data' => [
                    'camera' => 'Canon EOS R5',
                    'lens' => 'RF 24-70mm f/2.8L IS USM',
                    'iso' => 100,
                    'aperture' => 'f/8',
                    'shutter_speed' => '1/60',
                    'focal_length' => '35mm',
                ],
            ],
            [
                'title' => 'Urban Reflection',
                'description' => 'Modern skyscrapers reflected in the glass facade of a contemporary building.',
                'alt_text' => 'City skyscrapers reflected in glass building',
                'width' => 1440,
                'height' => 1920,
                'visibility' => 'public',
                'is_featured' => true,
                'tags' => ['Architecture', 'Urban', 'Abstract'],
                'exif_data' => [
                    'camera' => 'Sony A7R IV',
                    'lens' => 'FE 16-35mm f/2.8 GM',
                    'iso' => 200,
                    'aperture' => 'f/11',
                    'shutter_speed' => '1/125',
                    'focal_length' => '24mm',
                ],
            ],
            [
                'title' => 'Portrait in Golden Light',
                'description' => 'Natural light portrait captured during the golden hour with beautiful rim lighting.',
                'alt_text' => 'Portrait of a person backlit by golden hour sunlight',
                'width' => 1200,
                'height' => 1800,
                'visibility' => 'public',
                'is_featured' => false,
                'tags' => ['Portrait', 'Sunset', 'Nature'],
                'exif_data' => [
                    'camera' => 'Nikon Z9',
                    'lens' => '85mm f/1.4G',
                    'iso' => 400,
                    'aperture' => 'f/2.8',
                    'shutter_speed' => '1/200',
                    'focal_length' => '85mm',
                ],
            ],
            [
                'title' => 'Street Market Scene',
                'description' => 'Bustling local market with vendors selling fresh produce and handmade goods.',
                'alt_text' => 'Busy street market with vendors and customers',
                'width' => 1600,
                'height' => 1200,
                'visibility' => 'public',
                'is_featured' => true,
                'tags' => ['Street', 'Documentary', 'Travel'],
                'exif_data' => [
                    'camera' => 'Fujifilm X-T4',
                    'lens' => 'XF 23mm f/1.4 R',
                    'iso' => 800,
                    'aperture' => 'f/5.6',
                    'shutter_speed' => '1/80',
                    'focal_length' => '23mm',
                ],
            ],
            [
                'title' => 'Macro Flower Detail',
                'description' => 'Extreme close-up of a flower showing intricate petal textures and water droplets.',
                'alt_text' => 'Close-up macro shot of flower petals with water drops',
                'width' => 1800,
                'height' => 1200,
                'visibility' => 'public',
                'is_featured' => false,
                'tags' => ['Macro', 'Nature', 'Abstract'],
                'exif_data' => [
                    'camera' => 'Canon EOS R6',
                    'lens' => 'RF 100mm f/2.8L Macro IS USM',
                    'iso' => 320,
                    'aperture' => 'f/5.6',
                    'shutter_speed' => '1/160',
                    'focal_length' => '100mm',
                ],
            ],
            [
                'title' => 'Minimalist Architecture',
                'description' => 'Clean lines and geometric forms of modern minimalist architectural design.',
                'alt_text' => 'Minimalist building with clean geometric lines',
                'width' => 1920,
                'height' => 1440,
                'visibility' => 'public',
                'is_featured' => true,
                'tags' => ['Architecture', 'Minimalist', 'Urban'],
                'exif_data' => [
                    'camera' => 'Leica Q2',
                    'lens' => 'Summilux 28mm f/1.7',
                    'iso' => 100,
                    'aperture' => 'f/8',
                    'shutter_speed' => '1/250',
                    'focal_length' => '28mm',
                ],
            ],
            [
                'title' => 'Night City Lights',
                'description' => 'Long exposure capture of city traffic trails and illuminated buildings at night.',
                'alt_text' => 'Night cityscape with light trails from traffic',
                'width' => 2048,
                'height' => 1365,
                'visibility' => 'public',
                'is_featured' => true,
                'tags' => ['Night', 'Urban', 'Abstract'],
                'exif_data' => [
                    'camera' => 'Sony A7S III',
                    'lens' => 'FE 24-70mm f/2.8 GM',
                    'iso' => 1600,
                    'aperture' => 'f/8',
                    'shutter_speed' => '30s',
                    'focal_length' => '35mm',
                ],
            ],
            [
                'title' => 'Wildlife in Motion',
                'description' => 'Action shot of a bird in flight captured with precise timing and focus.',
                'alt_text' => 'Bird captured mid-flight with wings spread',
                'width' => 1600,
                'height' => 1200,
                'visibility' => 'public',
                'is_featured' => false,
                'tags' => ['Wildlife', 'Nature', 'Documentary'],
                'exif_data' => [
                    'camera' => 'Canon EOS R3',
                    'lens' => 'RF 600mm f/4L IS USM',
                    'iso' => 1250,
                    'aperture' => 'f/5.6',
                    'shutter_speed' => '1/2000',
                    'focal_length' => '600mm',
                ],
            ],
        ];

        foreach ($sampleImages as $index => $imageData) {
            // Assign to random user and album
            $user = $users->random();
            $album = $albums->where('user_id', $user->id)->random();

            // Create the image
            $image = Image::create([
                'user_id' => $user->id,
                'album_id' => $album ? $album->id : null,
                'title' => $imageData['title'],
                'description' => $imageData['description'],
                'alt_text' => $imageData['alt_text'],
                'filename' => Str::slug($imageData['title']) . '.jpg',
                'path' => 'images/sample/' . Str::slug($imageData['title']) . '.jpg',
                'disk' => 'local',
                'mime_type' => 'image/jpeg',
                'file_size' => rand(500000, 5000000), // 0.5MB to 5MB
                'hash' => hash('sha256', $imageData['title'] . time()),
                'width' => $imageData['width'],
                'height' => $imageData['height'],
                'visibility' => $imageData['visibility'],
                'license' => rand(0, 3) ? 'CC BY 4.0' : null,
                'allow_download' => true,
                'is_featured' => $imageData['is_featured'],
                'exif_data' => $imageData['exif_data'],
                'view_count' => rand(10, 1000),
                'download_count' => rand(0, 100),
                'thumbnails' => [
                    150 => 'thumbnails/' . Str::slug($imageData['title']) . '_150.jpg',
                    300 => 'thumbnails/' . Str::slug($imageData['title']) . '_300.jpg',
                    600 => 'thumbnails/' . Str::slug($imageData['title']) . '_600.jpg',
                    1200 => 'thumbnails/' . Str::slug($imageData['title']) . '_1200.jpg',
                ],
                'created_at' => now()->subDays(rand(0, 90)),
            ]);

            // Attach tags
            $imageTags = $tags->whereIn('name', $imageData['tags']);
            $image->tags()->attach($imageTags->pluck('id'));

            // Update tag usage counts
            foreach ($imageTags as $tag) {
                $tag->increment('usage_count');
            }
        }

        // Create additional random images
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $album = $albums->where('user_id', $user->id)->random();

            $image = Image::create([
                'user_id' => $user->id,
                'album_id' => rand(0, 3) ? $album->id : null,
                'title' => 'Sample Image ' . ($i + 9),
                'description' => 'This is a sample image for demonstration purposes.',
                'alt_text' => 'Sample image ' . ($i + 9),
                'filename' => 'sample-image-' . ($i + 9) . '.jpg',
                'path' => 'images/sample/sample-image-' . ($i + 9) . '.jpg',
                'disk' => 'local',
                'mime_type' => 'image/jpeg',
                'file_size' => rand(500000, 3000000),
                'hash' => hash('sha256', 'sample-image-' . ($i + 9) . time()),
                'width' => rand(1200, 2400),
                'height' => rand(800, 1800),
                'visibility' => collect(['public', 'unlisted', 'private'])->random(),
                'license' => rand(0, 2) ? 'CC BY 4.0' : null,
                'allow_download' => rand(0, 1),
                'is_featured' => rand(0, 10) === 0, // 10% chance
                'view_count' => rand(0, 500),
                'download_count' => rand(0, 50),
                'created_at' => now()->subDays(rand(0, 180)),
            ]);

            // Attach random tags
            $randomTags = $tags->random(rand(1, 4));
            $image->tags()->attach($randomTags->pluck('id'));

            foreach ($randomTags as $tag) {
                $tag->increment('usage_count');
            }
        }

        $this->command->info('Created ' . Image::count() . ' images');
    }
}
