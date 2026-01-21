<?php

namespace Database\Seeders;

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
            RoleSeeder::class,
            PermissionSeeder::class, // Must run after roles
            UserSeeder::class,
            DropdownOptionSeeder::class,
            RecordSeeder::class,
        ]);
    }
}
