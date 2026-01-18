<?php

namespace App\Repositories;

use App\Models\Form;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FormRepository extends BaseRepository
{
    public function __construct(Form $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a form by ID
     */
    public function findById(int $id): ?Form
    {
        return $this->model->find($id);
    }

    /**
     * Find a form by slug
     */
    public function findBySlug(string $slug): ?Form
    {
        return $this->model
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Find an active form by slug
     */
    public function findActiveBySlug(string $slug): ?Form
    {
        return $this->model
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all forms
     */
    public function getAll(): Collection
    {
        return $this->model
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all active forms
     */
    public function getAllActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get paginated forms
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('slug', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new form
     */
    public function create(array $data): Form
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing form by ID
     */
    public function updateById(int $id, array $data): bool
    {
        $form = $this->model->find($id);

        if (! $form) {
            return false;
        }

        return $form->update($data);
    }

    /**
     * Delete a form by ID
     */
    public function deleteById(int $id): bool
    {
        $form = $this->model->find($id);

        if (! $form) {
            return false;
        }

        return $form->delete();
    }

    /**
     * Check if a slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = $this->model->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get forms with submission count
     */
    public function getAllWithSubmissionCount(): Collection
    {
        return $this->model
            ->withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
