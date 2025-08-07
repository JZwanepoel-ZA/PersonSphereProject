<?php

namespace App\Http\Controllers;

use App\Models\Interests;

class InterestsController extends Controller
{
    public function index()
    {
        return Interests::orderBy('interest_name')->pluck('interest_name');
    }
}
