<?php

namespace App\Contracts;

use App\Models\Bribox;
use Illuminate\Database\Eloquent\Collection;

interface BriboxRepositoryInterface
{
    /**
     * Get all briboxes with relationships
     */
    public function getAll(): Collection;

    /**
     * Find bribox by ID
     */
    public function findById(int $id): ?Bribox;

    /**
     * Get briboxes with pagination and filters
     */
    public function getPaginated(array $filters = [], int $perPage = 20);
}
