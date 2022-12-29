<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([

            UserSeeder::class,
            RoleAndPermissionSeeder::class,
            ModelRoleSeeder::class,
            GeneralSeeder::class,
            SocialMediaSeeder::class,
            MetaSeeder::class,
            CanonicalSeeder::class,
            CategorySeeder::class,
            PostSeeder::class,
            TagSeeder::class,
            PostCategorySeeder::class,
            PostTagSeeder::class,

        ]);
    }
}
