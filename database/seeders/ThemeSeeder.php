<?php

namespace Database\Seeders;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default themes
        $themes = [
            [
                'name' => 'Default Light',
                'description' => 'Clean and modern light theme with blue accents',
                'is_public' => true,
                'is_default' => true,
                'colors' => [
                    'primary' => '#3b82f6',
                    'secondary' => '#6b7280',
                    'accent' => '#f59e0b',
                    'background' => '#ffffff',
                    'surface' => '#f9fafb',
                    'text' => '#111827',
                    'text-muted' => '#6b7280',
                    'border' => '#e5e7eb',
                    'success' => '#10b981',
                    'warning' => '#f59e0b',
                    'error' => '#ef4444',
                ],
                'typography' => [
                    'font-family' => 'Inter, ui-sans-serif, system-ui',
                    'font-size-base' => '16px',
                    'line-height' => '1.5',
                ],
                'spacing' => [
                    'xs' => '0.25rem',
                    'sm' => '0.5rem',
                    'md' => '1rem',
                    'lg' => '1.5rem',
                    'xl' => '2rem',
                ],
            ],
            [
                'name' => 'Dark Mode',
                'description' => 'Elegant dark theme perfect for low-light viewing',
                'is_public' => true,
                'is_default' => false,
                'colors' => [
                    'primary' => '#60a5fa',
                    'secondary' => '#9ca3af',
                    'accent' => '#fbbf24',
                    'background' => '#111827',
                    'surface' => '#1f2937',
                    'text' => '#f9fafb',
                    'text-muted' => '#9ca3af',
                    'border' => '#374151',
                    'success' => '#34d399',
                    'warning' => '#fbbf24',
                    'error' => '#f87171',
                ],
                'typography' => [
                    'font-family' => 'Inter, ui-sans-serif, system-ui',
                    'font-size-base' => '16px',
                    'line-height' => '1.5',
                ],
                'spacing' => [
                    'xs' => '0.25rem',
                    'sm' => '0.5rem',
                    'md' => '1rem',
                    'lg' => '1.5rem',
                    'xl' => '2rem',
                ],
            ],
            [
                'name' => 'Nature Green',
                'description' => 'Earthy green theme inspired by nature photography',
                'is_public' => true,
                'is_default' => false,
                'colors' => [
                    'primary' => '#059669',
                    'secondary' => '#6b7280',
                    'accent' => '#d97706',
                    'background' => '#ffffff',
                    'surface' => '#f0fdf4',
                    'text' => '#111827',
                    'text-muted' => '#6b7280',
                    'border' => '#d1fae5',
                    'success' => '#10b981',
                    'warning' => '#f59e0b',
                    'error' => '#ef4444',
                ],
                'typography' => [
                    'font-family' => 'Inter, ui-sans-serif, system-ui',
                    'font-size-base' => '16px',
                    'line-height' => '1.5',
                ],
                'spacing' => [
                    'xs' => '0.25rem',
                    'sm' => '0.5rem',
                    'md' => '1rem',
                    'lg' => '1.5rem',
                    'xl' => '2rem',
                ],
            ],
            [
                'name' => 'Warm Sunset',
                'description' => 'Warm orange and red tones reminiscent of golden hour',
                'is_public' => true,
                'is_default' => false,
                'colors' => [
                    'primary' => '#ea580c',
                    'secondary' => '#6b7280',
                    'accent' => '#eab308',
                    'background' => '#ffffff',
                    'surface' => '#fff7ed',
                    'text' => '#111827',
                    'text-muted' => '#6b7280',
                    'border' => '#fed7aa',
                    'success' => '#10b981',
                    'warning' => '#f59e0b',
                    'error' => '#ef4444',
                ],
                'typography' => [
                    'font-family' => 'Inter, ui-sans-serif, system-ui',
                    'font-size-base' => '16px',
                    'line-height' => '1.5',
                ],
                'spacing' => [
                    'xs' => '0.25rem',
                    'sm' => '0.5rem',
                    'md' => '1rem',
                    'lg' => '1.5rem',
                    'xl' => '2rem',
                ],
            ],
            [
                'name' => 'Ocean Blue',
                'description' => 'Cool blue theme inspired by ocean and sky photography',
                'is_public' => true,
                'is_default' => false,
                'colors' => [
                    'primary' => '#0891b2',
                    'secondary' => '#6b7280',
                    'accent' => '#06b6d4',
                    'background' => '#ffffff',
                    'surface' => '#f0f9ff',
                    'text' => '#111827',
                    'text-muted' => '#6b7280',
                    'border' => '#bae6fd',
                    'success' => '#10b981',
                    'warning' => '#f59e0b',
                    'error' => '#ef4444',
                ],
                'typography' => [
                    'font-family' => 'Inter, ui-sans-serif, system-ui',
                    'font-size-base' => '16px',
                    'line-height' => '1.5',
                ],
                'spacing' => [
                    'xs' => '0.25rem',
                    'sm' => '0.5rem',
                    'md' => '1rem',
                    'lg' => '1.5rem',
                    'xl' => '2rem',
                ],
            ],
        ];

        $users = User::all();

        foreach ($themes as $index => $themeData) {
            // Assign some themes to users, others as system themes
            $user = $index > 2 ? $users->random() : null;

            Theme::create([
                'user_id' => $user?->id,
                'name' => $themeData['name'],
                'description' => $themeData['description'],
                'is_public' => $themeData['is_public'],
                'is_default' => $themeData['is_default'],
                'colors' => $themeData['colors'],
                'typography' => $themeData['typography'],
                'spacing' => $themeData['spacing'],
                'layout' => [
                    'container_width' => 'max-w-7xl',
                    'grid_gap' => '1.5rem',
                    'border_radius' => '0.5rem',
                ],
                'usage_count' => rand(10, 500),
            ]);
        }

        $this->command->info('Created ' . count($themes) . ' themes');
    }
}
