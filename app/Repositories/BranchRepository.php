<?php

namespace App\Repositories;

use App\Contracts\BranchRepositoryInterface;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

class BranchRepository implements BranchRepositoryInterface
{
    /**
     * Get all branches with relationships
     */
    public function getAll(): Collection
    {
        return Branch::with('mainBranch')->get();
    }

    /**
     * Find branch by ID
     */
    public function findById(int $id): ?Branch
    {
        return Branch::with('mainBranch')->find($id);
    }

    /**
     * Get branches with pagination and filters
     */
    public function getPaginated(array $filters = [], int $perPage = 20)
    {
        $query = Branch::with('mainBranch');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('unit_name', 'like', "%{$search}%")
                  ->orWhere('branch_code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['main_branch_id'])) {
            $query->where('main_branch_id', $filters['main_branch_id']);
        }

        return $query->paginate($perPage);
    }
}
