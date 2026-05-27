<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class DataUpdated
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Model $model
    ) {}
}
