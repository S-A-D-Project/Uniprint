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
        $this->call([
            RoleTypesSeeder::class,
            StatusesSeeder::class,
            NewUsersSeeder::class,
            BaguioPrintshopsSeeder::class,
            ProductsSeeder::class,
            SampleOrdersSeeder::class,
        ]);
    }
}
