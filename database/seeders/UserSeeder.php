<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Gallery Administrator',
            'email' => 'admin@gallery.local',
            'password' => Hash::make('password'),
            'bio' => 'Platform administrator managing the gallery ecosystem.',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create editor user
        $editor = User::create([
            'name' => 'Jane Editor',
            'email' => 'editor@gallery.local',
            'password' => Hash::make('password'),
            'bio' => 'Professional photographer and content curator with 10 years of experience.',
            'website' => 'https://janeeditor.com',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $editor->assignRole('editor');

        // Create sample users
        $sampleUsers = [
            [
                'name' => 'Alex Rodriguez',
                'email' => 'alex@example.com',
                'bio' => 'Nature photographer capturing the beauty of landscapes around the world.',
                'website' => 'https://alexrodriguez.photography',
            ],
            [
                'name' => 'Sarah Chen',
                'email' => 'sarah@example.com',
                'bio' => 'Portrait photographer specializing in authentic human connections.',
                'website' => 'https://sarahchen.studio',
            ],
            [
                'name' => 'Marcus Johnson',
                'email' => 'marcus@example.com',
                'bio' => 'Street photographer documenting urban life and culture.',
                'website' => null,
            ],
            [
                'name' => 'Emily Watson',
                'email' => 'emily@example.com',
                'bio' => 'Fine art photographer exploring abstract concepts through visual storytelling.',
                'website' => 'https://emilywatson.art',
            ],
            [
                'name' => 'David Kim',
                'email' => 'david@example.com',
                'bio' => 'Architectural photographer showcasing modern design and urban spaces.',
                'website' => 'https://davidkim.photos',
            ],
        ];

        foreach ($sampleUsers as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'bio' => $userData['bio'],
                'website' => $userData['website'],
                'is_active' => true,
                'email_verified_at' => now(),
                'last_active_at' => now()->subDays(rand(0, 30)),
            ]);
            $user->assignRole('visitor');
        }

        $this->command->info('Created ' . User::count() . ' users');
    }
}
