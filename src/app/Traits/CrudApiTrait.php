<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * CrudApiTrait
 *
 * Consolidates common CRUD logic for API controllers
 * Eliminates 500+ lines of duplicated code across 11+ API controllers
 *
 * Usage in API Controller:
 *
 * class StudentApiController extends Controller
 * {
 *     use CrudApiTrait;
 *
 *     protected $model = Student::class;
 *     protected $indexQueryRelations = ['classroom', 'guardian'];
 *     protected $showQueryRelations = ['classroom', 'guardian', 'attendance'];
 *     protected $filterFields = ['classroom_id', 'status'];
 *     protected $perPage = 15;
 * }
 */
trait CrudApiTrait
{
    /**
     * Resolve the configured model class from the consuming controller.
     */
    protected function resolveModelClass(): string
    {
        /** @phpstan-ignore-next-line */
        if (empty($this->model)) {
            throw new \LogicException('Controller must define a protected $model property.');
        }

        /** @phpstan-ignore-next-line */
        if (! is_string($this->model) || ! class_exists($this->model)) {
            throw new \LogicException('Configured $model must be a valid model class name.');
        }

        /** @phpstan-ignore-next-line */
        return $this->model;
    }

    /**
     * Get paginated list with filtering
     */
    public function index(Request $request)
    {
        try {
            $modelClass = $this->resolveModelClass();
            $query = $modelClass::query();
            $indexQueryRelations = $this->indexQueryRelations ?? [];
            $filterFields = $this->filterFields ?? [];
            $perPage = $this->perPage ?? 15;

            // Load relations
            if (! empty($indexQueryRelations)) {
                $query->with($indexQueryRelations);
            }

            // Apply filters
            foreach ($filterFields as $field) {
                if ($request->filled($field)) {
                    // Support relation filters by convention: `{relation}_id` -> whereHas(relationPlural, id)
                    if (Str::endsWith($field, '_id')) {
                        $relationBase = substr($field, 0, -3); // remove _id
                        $relationMethod = Str::plural($relationBase);

                        if (method_exists($modelClass, $relationMethod) || method_exists((new $modelClass), $relationMethod)) {
                            $query->whereHas($relationMethod, function ($q) use ($request, $field) {
                                $q->where('id', $request->input($field));
                            });

                            continue;
                        }
                    }

                    $query->where($field, $request->input($field));
                }
            }

            // Search
            if ($request->filled('search')) {
                $searchTerm = '%'.$request->input('search').'%';
                $query->where(function ($q) use ($searchTerm) {
                    foreach ($this->getSearchableFields() as $field) {
                        if (str_contains($field, '.')) {
                            $this->applyNestedSearch($q, $field, $searchTerm);
                        } else {
                            $q->orWhere($field, 'LIKE', $searchTerm);
                        }
                    }
                });
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'id');
            $sortOrder = $request->input('sort_order', 'asc');
            $allowedSortColumns = array_merge($this->getSearchableFields(), ['id', 'created_at', 'updated_at']);
            if (in_array($sortBy, $allowedSortColumns)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Paginate
            $items = $query->paginate($perPage);

            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'paginated')) {
                return $this->paginated($items);
            }

            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'Data retrieved successfully',
                'data' => $items->items(),
                'pagination' => [
                    'per_page' => $items->perPage(),
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'total' => $items->total(),
                ],
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'error')) {
                return $this->error('Failed to retrieve items', ['error' => $e->getMessage()], 500);
            }

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Failed to retrieve items',
                'data' => ['error' => $e->getMessage()],
                'timestamp' => now(),
            ], 500);
        }
    }

    /**
     * Get single item
     */
    public function show($id)
    {
        try {
            $modelClass = $this->resolveModelClass();
            $query = $modelClass::query();
            $showQueryRelations = $this->showQueryRelations ?? [];

            if (! empty($showQueryRelations)) {
                $query->with($showQueryRelations);
            }

            $item = $query->findOrFail($id);

            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'success')) {
                return $this->success($item, 'Data retrieved successfully');
            }

            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'Data retrieved successfully',
                'data' => $item,
                'timestamp' => now(),
            ]);
        } catch (ModelNotFoundException $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'notFound')) {
                return $this->notFound('Item not found');
            }

            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'Item not found',
                'data' => null,
                'timestamp' => now(),
            ], 404);
        } catch (\Exception $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'error')) {
                return $this->error('Failed to retrieve item', ['error' => $e->getMessage()], 500);
            }

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Failed to retrieve item',
                'data' => ['error' => $e->getMessage()],
                'timestamp' => now(),
            ], 500);
        }
    }

    /**
     * Create new item
     */
    public function store(Request $request)
    {
        try {
            $modelClass = $this->resolveModelClass();
            $this->authorize('create', $modelClass);
            $data = $this->validateStoreRequest($request);

            $item = $modelClass::create($data);

            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'created')) {
                return $this->created($item, 'Item created successfully');
            }

            return response()->json([
                'success' => true,
                'code' => 201,
                'message' => 'Item created successfully',
                'data' => $item,
                'timestamp' => now(),
            ], 201);
        } catch (ValidationException $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'validationError')) {
                return $this->validationError($e->errors(), 'Validation failed');
            }

            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => 'Validation failed',
                'data' => $e->errors(),
                'timestamp' => now(),
            ], 422);
        } catch (AuthorizationException $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'error')) {
                return $this->error('Unauthorized', ['error' => $e->getMessage()], 403);
            }

            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => 'Unauthorized',
                'data' => ['error' => $e->getMessage()],
                'timestamp' => now(),
            ], 403);
        } catch (\Exception $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'error')) {
                return $this->error('Failed to create item', ['error' => $e->getMessage()], 500);
            }

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Failed to create item',
                'data' => ['error' => $e->getMessage()],
                'timestamp' => now(),
            ], 500);
        }
    }

    /**
     * Update existing item
     */
    public function update(Request $request, $id)
    {
        try {
            $modelClass = $this->resolveModelClass();
            $item = $modelClass::findOrFail($id);
            $this->authorize('update', $item);

            $data = $this->validateUpdateRequest($request);

            $item->update($data);

            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'success')) {
                return $this->success($item, 'Item updated successfully');
            }

            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'Item updated successfully',
                'data' => $item,
                'timestamp' => now(),
            ]);
        } catch (ModelNotFoundException $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'notFound')) {
                return $this->notFound('Item not found');
            }

            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'Item not found',
                'data' => null,
                'timestamp' => now(),
            ], 404);
        } catch (ValidationException $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'validationError')) {
                return $this->validationError($e->errors(), 'Validation failed');
            }

            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => 'Validation failed',
                'data' => $e->errors(),
                'timestamp' => now(),
            ], 422);
        } catch (AuthorizationException $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'error')) {
                return $this->error('Unauthorized', ['error' => $e->getMessage()], 403);
            }

            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => 'Unauthorized',
                'data' => ['error' => $e->getMessage()],
                'timestamp' => now(),
            ], 403);
        } catch (\Exception $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'error')) {
                return $this->error('Failed to update item', ['error' => $e->getMessage()], 500);
            }

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Failed to update item',
                'data' => ['error' => $e->getMessage()],
                'timestamp' => now(),
            ], 500);
        }
    }

    /**
     * Delete item (soft delete if available)
     */
    public function destroy($id)
    {
        try {
            $modelClass = $this->resolveModelClass();
            $item = $modelClass::findOrFail($id);
            $this->authorize('delete', $item);

            // Use soft delete if available
            if (in_array(SoftDeletes::class, class_uses_recursive($item))) {
                $item->delete(); // Soft delete
            } else {
                $item->delete(); // Hard delete
            }

            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'success')) {
                return $this->success(null, 'Item deleted successfully');
            }

            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'Item deleted successfully',
                'data' => null,
                'timestamp' => now(),
            ]);
        } catch (ModelNotFoundException $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'notFound')) {
                return $this->notFound('Item not found');
            }

            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'Item not found',
                'data' => null,
                'timestamp' => now(),
            ], 404);
        } catch (AuthorizationException $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'error')) {
                return $this->error('Unauthorized', ['error' => $e->getMessage()], 403);
            }

            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => 'Unauthorized',
                'data' => ['error' => $e->getMessage()],
                'timestamp' => now(),
            ], 403);
        } catch (\Exception $e) {
            /** @phpstan-ignore-next-line */
            if (method_exists($this, 'error')) {
                return $this->error('Failed to delete item', ['error' => $e->getMessage()], 500);
            }

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Failed to delete item',
                'data' => ['error' => $e->getMessage()],
                'timestamp' => now(),
            ], 500);
        }
    }

    /**
     * Override in controller to define store validation rules
     */
    protected function validateStoreRequest(Request $request): array
    {
        return $request->validate($this->storeRules($request));
    }

    /**
     * Override in controller to define update validation rules
     */
    protected function validateUpdateRequest(Request $request): array
    {
        return $request->validate($this->updateRules($request));
    }

    /**
     * Override in controller - return store validation rules
     */
    protected function storeRules(Request $request): array
    {
        return [];
    }

    /**
     * Override in controller - return update validation rules
     */
    protected function updateRules(Request $request): array
    {
        return [];
    }

    /**
     * Apply nested search for dot-notation fields (e.g., employee.user.name)
     */
    protected function applyNestedSearch($query, string $field, string $searchTerm): void
    {
        $segments = explode('.', $field);
        $column = array_pop($segments); // Last segment is the column
        $relation = implode('.', $segments); // Remaining is the relation path

        $query->orWhereHas($relation, function ($q) use ($column, $searchTerm) {
            // Check if there's still a dot in the column (multi-level nested)
            if (str_contains($column, '.')) {
                $this->applyNestedSearch($q, $column, $searchTerm);
            } else {
                $q->where($column, 'LIKE', $searchTerm);
            }
        });
    }

    /**
     * Override in controller - return searchable fields
     */
    protected function getSearchableFields(): array
    {
        return ['name', 'email'];
    }
}
