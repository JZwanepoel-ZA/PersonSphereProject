<?php

namespace App\Http\Controllers;

use App\Models\Languages;

class LanguagesController extends Controller
{
    public function index()
    {
        return Languages::orderBy('language_name')->get(['id', 'language_name']);
    }
}
