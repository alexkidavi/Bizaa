<?php

namespace App\Http\Controllers;

use App\Models\WarningType;

class WarningTypeController extends HrmLookupTypeController
{
    protected string $modelClass = WarningType::class;
    protected string $label = 'Warning type';
}
