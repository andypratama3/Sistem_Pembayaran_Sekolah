<?php

namespace App\Traits;

trait Filterable
{
    public function scopeFilter($query, array $filters)
    {
        foreach ($filters as $field => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            if (is_array($value)) {
                [$operator, $filterValue] = $value;
                $query->where($field, $operator, $filterValue);
            } else {
                $query->where($field, '=', $value);
            }
        }

        return $query;
    }
}
