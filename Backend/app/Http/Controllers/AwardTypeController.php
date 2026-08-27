<?php

namespace App\Http\Controllers;

use App\Models\AwardType;

class AwardTypeController extends HrmLookupTypeController
{
    protected string $modelClass = AwardType::class;
    protected string $label = 'Award type';
}
