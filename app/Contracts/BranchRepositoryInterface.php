<?php

namespace App\Contracts;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

interface BranchRepositoryInterface
{
    /**
     * Get all branches with relationships
     */
    public function getAll(): Collection;

    /**
     * Find branch by ID
     */
    public function findById(int $id): ?Branch;

    /**
     * Get branches with pagination and filters
     */
    public function getPaginated(array $filters = [], int $perPage = 20);
}
