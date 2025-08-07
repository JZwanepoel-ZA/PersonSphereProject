<?php

namespace Database\Seeders;

use App\Models\Languages;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = ['Afrikaans', 'English', 'French', 'Portuguese'
];
        foreach ($languages as $language) {
            $newLanguage = new Languages();
            $newLanguage->language_name = $language;
            $newLanguage->save();
        }
    }
}
