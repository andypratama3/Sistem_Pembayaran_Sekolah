<?php

namespace App\Events;

use App\Models\EmployeeSalaryConfiguration;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class EmployeeSalaryConfigUpdated
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public EmployeeSalaryConfiguration $salaryConfig
    ) {}
}
