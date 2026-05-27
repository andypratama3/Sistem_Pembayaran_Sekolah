<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ProcessStudentsDapodikService
{
    protected string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function dispatchFromUpload(UploadedFile $file, string $taskId): array
    {
        return [
            'batch_id' => '',
            'import_id' => '',
            'total_rows' => 0,
        ];
    }
}
