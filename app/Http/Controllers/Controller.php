<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Auditable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use ApiResponse;
    use Auditable;
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
}
