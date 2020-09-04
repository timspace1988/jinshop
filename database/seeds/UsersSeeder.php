<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //laravel has lready create User model's factory file, here we just use it to create 100 users 
        factory(\App\Models\User::class, 100)->create();
    }
}
