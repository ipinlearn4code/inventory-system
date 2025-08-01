<?php

namespace App\Contracts;

use App\Models\AssignmentLetter;
use Illuminate\Database\Eloquent\Collection;

interface AssignmentLetterRepositoryInterface
{
    /**
     * Find assignment letter by ID
     */
    public function findById(int $id): ?AssignmentLetter;

    /**
     * Get all letters for a specific assignment
     */
    public function getByAssignmentId(int $assignmentId): Collection;

    /**
     * Create a new assignment letter
     */
    public function create(array $data): AssignmentLetter;

    /**
     * Update an assignment letter
     */
    public function update(int $id, array $data): AssignmentLetter;

    /**
     * Delete an assignment letter
     */
    public function delete(int $id): bool;

    /**
     * Find letter by assignment ID and type
     */
    public function findByAssignmentAndType(int $assignmentId, string $type): ?AssignmentLetter;
}
