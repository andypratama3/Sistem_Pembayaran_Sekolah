<?php

namespace App\Events;

use App\Models\AcademicYear;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class AcademicYearConfigUpdated
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public AcademicYear $academicYear
    ) {}
}
