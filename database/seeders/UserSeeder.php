<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();

        $new_user = new User();
        $new_user->first_name = $faker->firstName;
        $new_user->last_name = $faker->lastName;
        $new_user->email = 'admin@personsphere.co.za';
        $new_user->email_verified_at = now();
        $new_user->password = Hash::make('admin123');
        $new_user->save();
    }
}
