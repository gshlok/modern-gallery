<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class TestImageAlias extends Command
{
    protected $signature = 'test:image-alias';
    protected $description = 'Test Intervention Image Facade Alias';

    public function handle()
    {
        try {
            $this->info('Intervention Image alias exists!');
            $img = Image::canvas(100, 100);
            $this->info('Canvas created successfully!');
        } catch (\Throwable $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }
}
