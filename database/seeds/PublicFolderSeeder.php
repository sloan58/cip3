<?php

use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Illuminate\Support\Facades\File;
use Laracasts\TestDummy\Factory as TestDummy;

class PublicFolderSeederTableSeeder extends Seeder
{
    public function run()
    {
        File::makeDirectory(storage_path("app/public/backgrounds"), 0777, true, true);
        File::makeDirectory(storage_path("app/public/screenshots"), 0777, true, true);
    }
}
