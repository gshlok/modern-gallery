<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Nature', 'color' => '#10b981', 'description' => 'Natural landscapes, wildlife, and outdoor photography'],
            ['name' => 'Portrait', 'color' => '#3b82f6', 'description' => 'Human portraits and character studies'],
            ['name' => 'Architecture', 'color' => '#6b7280', 'description' => 'Buildings, structures, and architectural details'],
            ['name' => 'Street', 'color' => '#f59e0b', 'description' => 'Urban life, street scenes, and candid moments'],
            ['name' => 'Abstract', 'color' => '#8b5cf6', 'description' => 'Abstract compositions and experimental photography'],
            ['name' => 'Landscape', 'color' => '#059669', 'description' => 'Scenic views, mountains, forests, and natural vistas'],
            ['name' => 'Macro', 'color' => '#dc2626', 'description' => 'Close-up photography revealing intricate details'],
            ['name' => 'Black & White', 'color' => '#374151', 'description' => 'Monochromatic photography emphasizing contrast'],
            ['name' => 'Travel', 'color' => '#0891b2', 'description' => 'Photography from travels and cultural exploration'],
            ['name' => 'Wildlife', 'color' => '#84cc16', 'description' => 'Animals in their natural habitats'],
            ['name' => 'Urban', 'color' => '#64748b', 'description' => 'City life, metropolitan scenes, and urban culture'],
            ['name' => 'Vintage', 'color' => '#a855f7', 'description' => 'Retro-styled or historically-themed photography'],
            ['name' => 'Minimalist', 'color' => '#06b6d4', 'description' => 'Simple compositions with clean, uncluttered aesthetics'],
            ['name' => 'Documentary', 'color' => '#ef4444', 'description' => 'Real-life events and storytelling through photography'],
            ['name' => 'Fashion', 'color' => '#ec4899', 'description' => 'Style, clothing, and fashion photography'],
            ['name' => 'Food', 'color' => '#f97316', 'description' => 'Culinary photography and food styling'],
            ['name' => 'Night', 'color' => '#1e293b', 'description' => 'Nighttime photography and low-light captures'],
            ['name' => 'Sunset', 'color' => '#fb923c', 'description' => 'Golden hour and sunset photography'],
            ['name' => 'Water', 'color' => '#0284c7', 'description' => 'Oceans, rivers, lakes, and water-related photography'],
            ['name' => 'Mountain', 'color' => '#7c3aed', 'description' => 'Mountain ranges, peaks, and alpine photography'],
        ];

        foreach ($tags as $tagData) {
            Tag::create($tagData);
        }

        $this->command->info('Created ' . count($tags) . ' tags');
    }
}
