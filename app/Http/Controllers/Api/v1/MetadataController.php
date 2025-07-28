<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Contracts\UserRepositoryInterface;
use App\Models\Branch;
use App\Models\Bribox;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MetadataController extends BaseApiController
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get users list with pagination
     */
    public function users(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'department_id' => $request->input('departmentId'),
            'branch_id' => $request->input('branchId'),
        ];

        $perPage = $request->input('perPage', 20);
        $users = $this->userRepository->getPaginated($filters, $perPage);

        $data = collect($users->items())->map(function ($user) {
            return [
                'userId' => $user->user_id,
                'pn' => $user->pn,
                'name' => $user->name,
                'position' => $user->position,
                'department' => [
                    'departmentId' => $user->department->department_id ?? null,
                    'name' => $user->department->name ?? null,
                ],
                'branch' => [
                    'branchId' => $user->branch->branch_id ?? null,
                    'unitName' => $user->branch->unit_name ?? null,
                    'branchCode' => $user->branch->branch_code ?? null,
                ],
                'activeDevicesCount' => $user->deviceAssignments->count(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'total' => $users->total()
            ]
        ]);
    }

    /**
     * Get branches list
     */
    public function branches(Request $request): JsonResponse
    {
        $branches = Branch::with('mainBranch')->get();

        $data = $branches->map(function ($branch) {
            return [
                'branchId' => $branch->branch_id,
                'unitName' => $branch->unit_name,
                'branchCode' => $branch->branch_code,
                'address' => $branch->address,
                'mainBranch' => [
                    'mainBranchId' => $branch->mainBranch->main_branch_id ?? null,
                    'name' => $branch->mainBranch->main_branch_name ?? null,
                ],
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Get categories (briboxes) list
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = Bribox::with('category')->get();

        $data = $categories->map(function ($bribox) {
            return [
                'briboxId' => $bribox->bribox_id,
                'name' => $bribox->name,
                'description' => $bribox->description,
                'category' => [
                    'categoryId' => $bribox->category->briboxes_category_id ?? null,
                    'name' => $bribox->category->name ?? null,
                ],
            ];
        });

        return response()->json(['data' => $data]);
    }
}
