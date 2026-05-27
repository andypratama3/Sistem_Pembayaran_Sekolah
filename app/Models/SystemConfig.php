<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * SystemConfig Model
 *
 * Menyimpan konfigurasi sistem yang dapat diubah melalui UI.
 * Setiap config memiliki:
 * - grup: Grouping (attendance, payroll, system, integration, etc)
 * - key: Unique identifier
 * - label: Human-readable label
 * - nilai: Config value
 * - tipe: Type (text, number, boolean, date, json)
 * - deskripsi: Description
 * - is_editable: Can user edit this?
 * - urutan: Sort order
 *
 * @property string $id
 * @property string $grup
 * @property string $key
 * @property string $label
 * @property string|null $nilai
 * @property string $tipe
 * @property string|null $deskripsi
 * @property bool $is_editable
 * @property int $urutan
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SystemConfig extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'system_configs';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'grup',
        'key',
        'label',
        'nilai',
        'tipe',
        'deskripsi',
        'is_editable',
        'urutan',
    ];

    protected $casts = [
        'is_editable' => 'boolean',
        'urutan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [];

    // ============ SCOPES ============

    /**
     * Filter by grup
     */
    public function scopeByGrup($query, string $grup)
    {
        return $query->where('grup', $grup);
    }

    /**
     * Filter by key
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Filter editable configs
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    /**
     * Filter non-editable configs
     */
    public function scopeNonEditable($query)
    {
        return $query->where('is_editable', false);
    }

    /**
     * Order by grup and urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('grup')->orderBy('urutan');
    }

    // ============ METHODS ============

    /**
     * Get nilai dengan type casting
     */
    public function getNilaiAttribute($value)
    {
        return $this->castValue($value, $this->tipe);
    }

    /**
     * Get nilai as original (without casting)
     */
    public function getNilaiRawAttribute()
    {
        return $this->attributes['nilai'] ?? null;
    }

    /**
     * Cast value berdasarkan type
     */
    public function castValue($value, $type = null)
    {
        $type = $type ?? $this->tipe ?? 'text';

        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => (bool) $value,
            'number' => is_numeric($value) ? (float) $value : $value,
            'integer' => is_numeric($value) ? (int) $value : $value,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            'date' => is_string($value) ? Carbon::parse($value) : $value,
            default => $value,
        };
    }

    /**
     * Get nilai dari cache atau database
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("system_config_{$key}", now()->addHours(24), function () use ($key, $default) {
            $config = static::byKey($key)->first();

            if ($config) {
                return $config->castValue($config->getNilaiRawAttribute(), $config->tipe);
            }

            return $default;
        });
    }

    /**
     * Set nilai dan clear cache
     */
    public function setNilaiAndClearCache($value)
    {
        $this->update(['nilai' => $value]);
        Cache::forget("system_config_{$this->key}");

        return $this;
    }

    /**
     * Get all configs grouped by grup
     */
    public static function getGrouped()
    {
        return static::ordered()->get()->groupBy('grup');
    }

    /**
     * Get all configs as key-value array
     */
    public static function getAsArray()
    {
        return static::get()->mapWithKeys(function ($config) {
            return [$config->key => $config->castValue($config->getNilaiRawAttribute(), $config->tipe)];
        })->toArray();
    }

    /**
     * Get all configs as key-value array (raw values)
     */
    public static function getAsArrayRaw()
    {
        return static::get()->mapWithKeys(function ($config) {
            return [$config->key => $config->getNilaiRawAttribute()];
        })->toArray();
    }

    /**
     * Validate config value
     */
    public function validateValue($value): array
    {
        $errors = [];

        switch ($this->tipe) {
            case 'number':
                if (! is_numeric($value)) {
                    $errors[] = "{$this->label} harus berupa angka";
                }
                break;

            case 'integer':
                // Accept numeric integer-like values or actual integers
                if (filter_var($value, FILTER_VALIDATE_INT) === false && ! is_int($value)) {
                    $errors[] = "{$this->label} harus berupa angka bulat";
                }
                break;

            case 'boolean':
                if (! in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'])) {
                    $errors[] = "{$this->label} harus berupa boolean";
                }
                break;

            case 'date':
                try {
                    Carbon::parse($value);
                } catch (\Exception $e) {
                    $errors[] = "{$this->label} harus berupa tanggal yang valid";
                }
                break;

            case 'json':
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errors[] = "{$this->label} harus berupa JSON yang valid";
                    }
                }
                break;
        }

        return $errors;
    }

    /**
     * Check if config is valid
     */
    public function isValid($value): bool
    {
        return empty($this->validateValue($value));
    }

    /**
     * Get display value (formatted for UI)
     */
    public function getDisplayValue()
    {
        $value = $this->castValue($this->getNilaiRawAttribute(), $this->tipe);

        return match ($this->tipe) {
            'boolean' => $value ? 'Ya' : 'Tidak',
            'date' => $value ? $value->format('d M Y') : '-',
            'json' => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            default => $value,
        };
    }

    /**
     * Get input type untuk form
     */
    public function getInputType(): string
    {
        return match ($this->tipe) {
            'number' => 'number',
            'integer' => 'number',
            'boolean' => 'checkbox',
            'date' => 'date',
            'json' => 'textarea',
            default => 'text',
        };
    }

    /**
     * Get input attributes untuk form
     */
    public function getInputAttributes(): array
    {
        $attributes = [
            'class' => 'form-control',
            'id' => "config_{$this->key}",
            'name' => "configs[{$this->id}][nilai]",
        ];

        if ($this->tipe === 'number') {
            $attributes['step'] = '0.01';
        }

        if ($this->tipe === 'integer') {
            $attributes['step'] = '1';
        }

        if ($this->tipe === 'json') {
            $attributes['rows'] = '5';
        }

        if (! $this->is_editable) {
            $attributes['disabled'] = 'disabled';
        }

        return $attributes;
    }

    // ============ EVENTS ============

    /**
     * Clear cache ketika config diupdate
     */
    protected static function booted()
    {
        static::updated(function ($config) {
            Cache::forget("system_config_{$config->key}");
        });

        static::deleted(function ($config) {
            Cache::forget("system_config_{$config->key}");
        });
    }
}
