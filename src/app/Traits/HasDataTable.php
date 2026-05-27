<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;

trait HasDataTable
{
    /**
     * Build a standardized Eloquent DataTable with optional global search.
     */
    protected function buildDataTable(Builder $query, array $searchableColumns = [], array $relationColumns = []): EloquentDataTable
    {
        /** @var EloquentDataTable $dataTable */
        $dataTable = DataTables::eloquent($query);

        if (! empty($searchableColumns)) {
            $this->applySearch($dataTable, $searchableColumns, $relationColumns);
        }

        return $dataTable;
    }

    protected function applySearch(EloquentDataTable $dataTable, array $searchableColumns, array $relationColumns = []): EloquentDataTable
    {
        // Handle global search by grouping all searchable columns in one OR clause.
        $dataTable->filter(function ($query) use ($searchableColumns, $relationColumns) {
            $keyword = trim((string) request('search.value', ''));

            if ($keyword === '') {
                return;
            }

            $query->where(function ($q) use ($searchableColumns, $keyword) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$keyword}%");
                }
            });

            foreach ($relationColumns as $relation => $columns) {
                $qColumns = is_array($columns) ? $columns : [$columns];
                $query->orWhereHas($relation, function ($relationQuery) use ($qColumns, $keyword) {
                    foreach ($qColumns as $column) {
                        $relationQuery->orWhere($column, 'like', "%{$keyword}%");
                    }
                });
            }
        }, true); // ✅ true = override default Yajra search

        return $dataTable;
    }
}
