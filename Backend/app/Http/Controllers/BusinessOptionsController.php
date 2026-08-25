<?php

namespace App\Http\Controllers;

use App\Models\County;
use App\Models\Industry;

class BusinessOptionsController extends Controller
{
    public function getCounties()
    {
        return response()->json(County::orderBy('name')->get());
    }

    public function getIndustries()
    {
        return response()->json(Industry::orderBy('name')->get());
    }
}
