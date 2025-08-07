<?php

namespace Database\Seeders;

use App\Models\Interests;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $interests = ['Painting', 'Photography', 'Sports', 'Gaming'];
        foreach ($interests as $interest) {
            $newInterest = new Interests();
            $newInterest->interest_name = $interest;
            $newInterest->save();
        }
    }
}
