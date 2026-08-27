<?php

namespace App\Http\Controllers;

use App\Models\TerminationType;

class TerminationTypeController extends HrmLookupTypeController
{
    protected string $modelClass = TerminationType::class;
    protected string $label = 'Termination type';
}
