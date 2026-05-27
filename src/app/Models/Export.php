<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends Model
{
    use HasAuditLog, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'instance_id',
        'exported_by',
        'file_path',
        'format',
        'original_filename',
        'file_size',
        'export_metadata',
    ];

    protected $casts = [
        'export_metadata' => 'array',
    ];

    // Relations
    public function instance(): BelongsTo
    {
        return $this->belongsTo(TemplateInstance::class, 'instance_id', 'id');
    }

    public function exportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by', 'id');
    }

    // Scopes
    public function scopeFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    public function scopePdf($query)
    {
        return $query->where('format', 'pdf');
    }

    public function scopeExcel($query)
    {
        return $query->where('format', 'xlsx');
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Methods
    public function getUrl(): string
    {
        // Return S3 URL or local path download URL
        if (str_starts_with($this->file_path, 's3://')) {
            return \Storage::disk('s3')->url($this->file_path);
        }

        return route('export.download', $this->id);
    }

    public function getFileExtension(): string
    {
        return match ($this->format) {
            'pdf' => 'pdf',
            'xlsx' => 'xlsx',
            default => 'bin',
        };
    }

    public static function getValidFormats(): array
    {
        return ['pdf', 'xlsx'];
    }
}
