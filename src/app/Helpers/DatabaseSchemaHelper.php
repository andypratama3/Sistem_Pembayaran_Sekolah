<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Schema;

/**
 * DatabaseSchemaHelper
 *
 * Helper untuk mengecek keberadaan tabel dan kolom di database
 * sebelum melakukan query, untuk menghindari error "Column not found"
 */
class DatabaseSchemaHelper
{
    /**
     * Check if table exists
     */
    public static function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if column exists in table
     */
    public static function columnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if multiple columns exist in table
     */
    public static function columnsExist(string $table, array $columns): bool
    {
        try {
            return Schema::hasColumns($table, $columns);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all columns in table
     */
    public static function getColumns(string $table): array
    {
        try {
            return Schema::getColumnListing($table);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if table has any of the columns
     */
    public static function hasAnyColumn(string $table, array $columns): bool
    {
        try {
            $tableColumns = Schema::getColumnListing($table);
            foreach ($columns as $column) {
                if (in_array($column, $tableColumns)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get column type
     */
    public static function getColumnType(string $table, string $column): ?string
    {
        try {
            $columns = Schema::getColumns($table);
            foreach ($columns as $col) {
                if ($col['name'] === $column) {
                    return $col['type'];
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if column is nullable
     */
    public static function isColumnNullable(string $table, string $column): bool
    {
        try {
            $columns = Schema::getColumns($table);
            foreach ($columns as $col) {
                if ($col['name'] === $column) {
                    return $col['nullable'] ?? false;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate table structure
     *
     * @param  array  $requiredColumns  Format: ['column_name' => 'type', ...]
     * @return array ['valid' => bool, 'missing' => [], 'errors' => []]
     */
    public static function validateTableStructure(string $table, array $requiredColumns): array
    {
        $result = [
            'valid' => true,
            'missing' => [],
            'errors' => [],
        ];

        // Check if table exists
        if (! self::tableExists($table)) {
            $result['valid'] = false;
            $result['errors'][] = "Table '{$table}' does not exist";

            return $result;
        }

        // Check required columns
        $tableColumns = self::getColumns($table);
        foreach ($requiredColumns as $column => $type) {
            if (! in_array($column, $tableColumns)) {
                $result['valid'] = false;
                $result['missing'][] = $column;
            }
        }

        return $result;
    }

    /**
     * Get table info
     */
    public static function getTableInfo(string $table): array
    {
        return [
            'exists' => self::tableExists($table),
            'columns' => self::getColumns($table),
            'column_count' => count(self::getColumns($table)),
        ];
    }

    /**
     * Check multiple tables
     */
    public static function checkTables(array $tables): array
    {
        $result = [];
        foreach ($tables as $table) {
            $result[$table] = [
                'exists' => self::tableExists($table),
                'columns' => self::getColumns($table),
            ];
        }

        return $result;
    }
}
