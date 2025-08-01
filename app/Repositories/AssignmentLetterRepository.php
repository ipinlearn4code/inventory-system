<?php

namespace App\Repositories;

use App\Contracts\AssignmentLetterRepositoryInterface;
use App\Models\AssignmentLetter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AssignmentLetterRepository implements AssignmentLetterRepositoryInterface
{
    /**
     * Find assignment letter by ID
     */
    public function findById(int $id): ?AssignmentLetter
    {
        return AssignmentLetter::find($id);
    }

    /**
     * Get all letters for a specific assignment
     */
    public function getByAssignmentId(int $assignmentId): Collection
    {
        return AssignmentLetter::where('assignment_id', $assignmentId)->get();
    }

    /**
     * Create a new assignment letter
     */
    public function create(array $data): AssignmentLetter
    {
        $data['created_by'] = $data['created_by'] ?? Auth::id();
        $data['created_at'] = $data['created_at'] ?? now();
        
        return AssignmentLetter::create($data);
    }

    /**
     * Update an assignment letter
     */
    public function update(int $id, array $data): AssignmentLetter
    {
        $letter = AssignmentLetter::findOrFail($id);
        
        $data['updated_by'] = $data['updated_by'] ?? Auth::id();
        $data['updated_at'] = $data['updated_at'] ?? now();
        
        $letter->update($data);
        
        return $letter->fresh();
    }

    /**
     * Delete an assignment letter
     */
    public function delete(int $id): bool
    {
        $letter = AssignmentLetter::find($id);
        
        if (!$letter) {
            return false;
        }
        
        return $letter->delete();
    }

    /**
     * Find letter by assignment ID and type
     */
    public function findByAssignmentAndType(int $assignmentId, string $type): ?AssignmentLetter
    {
        return AssignmentLetter::where('assignment_id', $assignmentId)
            ->where('letter_type', $type)
            ->first();
    }
}
