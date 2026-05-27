<?php

namespace App\Traits;

trait Searchable
{
    public function scopeSearch($query, string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $columns = $this->searchable ?? [];

        $query->where(function ($query) use ($term, $columns) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column);
                    $query->orWhereHas($relation, function ($query) use ($field, $term) {
                        $query->where($field, 'LIKE', "%$term%");
                    });
                } else {
                    $query->orWhere($column, 'LIKE', "%$term%");
                }
            }
        });

        return $query;
    }
}
