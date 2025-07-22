<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    
    public function findByPn(string $pn): ?User;
    
    public function getAll(): Collection;
    
    public function getPaginated(array $filters = [], int $perPage = 20);
    
    public function getUsersByBranch(int $branchId): Collection;
    
    public function getUsersByDepartment(int $departmentId): Collection;
    
    public function searchUsers(string $search): Collection;
}
