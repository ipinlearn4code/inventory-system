<?php

namespace App\Repositories;

use App\Contracts\BriboxRepositoryInterface;
use App\Models\Bribox;
use Illuminate\Database\Eloquent\Collection;

class BriboxRepository implements BriboxRepositoryInterface
{
    /**
     * Get all briboxes with relationships
     */
    public function getAll(): Collection
    {
        return Bribox::with('category')->get();
    }

    /**
     * Find bribox by ID
     */
    public function findById(int $id): ?Bribox
    {
        return Bribox::with('category')->find($id);
    }

    /**
     * Get briboxes with pagination and filters
     */
    public function getPaginated(array $filters = [], int $perPage = 20)
    {
        $query = Bribox::with('category');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->paginate($perPage);
    }
}
