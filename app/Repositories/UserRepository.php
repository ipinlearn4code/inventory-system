<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::with(['department', 'branch', 'deviceAssignments' => function ($q) {
            $q->whereNull('returned_date');
        }])->find($id);
    }

    public function findByPn(string $pn): ?User
    {
        if (empty($pn)) return null;
        return User::where('pn', $pn)->first();
    }

    public function getAll(): Collection
    {

        return User::with(['department', 'branch'])->get();
    }

    public function getPaginated(array $filters = [], int $perPage = 20)
    {
        $query = User::with([
            'department',
            'branch',
            'deviceAssignments' => function ($q) {
                $q->whereNull('returned_date');
            }
        ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            // Escape % and _ for LIKE, and use parameter binding
            $escaped = addcslashes($search, '%_\\');
            $like = '%' . $escaped . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like, 'and', false)
                    ->orWhere('pn', 'like', $like, 'and', false)
                    ->orWhere('position', 'like', $like, 'and', false);
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query->paginate($perPage);
    }

    public function getUsersByBranch(int $branchId): Collection
    {
        return User::with(['department'])
            ->where('branch_id', $branchId)
            ->get();
    }

    public function getUsersByDepartment(int $departmentId): Collection
    {
        return User::with(['branch'])
            ->where('department_id', $departmentId)
            ->get();
    }

    public function searchUsers(string $search): Collection
    {
        return User::with(['department', 'branch'])
            ->where(function ($q) use ($search) {
                // Escape % and _ for LIKE
                $escaped = addcslashes($search, '%_\\');
                $like = "%{$escaped}%";
                $q->where('name', 'like', $like, 'and', false)
                    ->orWhere('pn', 'like', $like, 'and', false)
                    ->orWhere('position', 'like', $like, 'and', false);
            })
            ->get();
    }
}
