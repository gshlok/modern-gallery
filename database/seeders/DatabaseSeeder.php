<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Gallery Platform database seeding...');

        // Seed in order of dependencies
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            TagSeeder::class,
            AlbumSeeder::class,
            ImageSeeder::class,
            CommentSeeder::class,
            LikeAndAnalyticsSeeder::class,
            ThemeSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   • Users: ' . \App\Models\User::count());
        $this->command->info('   • Albums: ' . \App\Models\Album::count());
        $this->command->info('   • Images: ' . \App\Models\Image::count());
        $this->command->info('   • Tags: ' . \App\Models\Tag::count());
        $this->command->info('   • Comments: ' . \App\Models\Comment::count());
        $this->command->info('   • Likes: ' . \App\Models\Like::count());
        $this->command->info('   • Analytics Events: ' . \App\Models\AnalyticsEvent::count());
        $this->command->info('   • Themes: ' . \App\Models\Theme::count());
        $this->command->info('');
        $this->command->info('🔐 Default Admin Account:');
        $this->command->info('   Email: admin@gallery.local');
        $this->command->info('   Password: password');
        $this->command->info('');
        $this->command->info('👥 Sample User Accounts:');
        $this->command->info('   • editor@gallery.local (Editor role)');
        $this->command->info('   • alex@example.com (Visitor role)');
        $this->command->info('   • sarah@example.com (Visitor role)');
        $this->command->info('   All passwords: password');
        $this->command->info('');
        $this->command->info('🎨 Ready to explore the gallery platform!');
    }
}
