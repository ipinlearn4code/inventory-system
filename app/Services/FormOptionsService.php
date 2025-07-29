<?php

namespace App\Services;

use App\Contracts\FormOptionsServiceInterface;
use App\Models\Device;
use App\Models\User;
use App\Models\Branch;
use App\Models\Bribox;
use App\Models\BriboxesCategory;
use App\Models\Department;

class FormOptionsService implements FormOptionsServiceInterface
{
    /**
     * Get all form options for device creation/editing
     */
    public function getDeviceFormOptions(?string $search = null, ?string $field = null): array
    {
        // If specific field is requested
        if ($field) {
            return [
                $field => $this->getFieldOptions($field, $search)
            ];
        }

        // Return all options
        return [
            'brands' => $this->getBrandOptions($search),
            'brandNames' => $this->getBrandNameOptions($search),
            'briboxes' => $this->getBriboxOptions($search),
            'conditions' => $this->getConditionOptions(),
            'statuses' => $this->getStatusOptions(),
            // 'categories' => $this->getCategoryOptions($search),
        ];
    }

    /**
     * Get all form options for device assignment creation/editing
     */
    public function getDeviceAssignmentFormOptions(?string $search = null, ?string $field = null): array
    {
        // If specific field is requested
        if ($field) {
            return [
                $field => $this->getFieldOptions($field, $search)
            ];
        }

        // Return all options
        return [
            'devices' => $this->getAvailableDeviceOptions($search),
            'users' => $this->getUserOptions($search),
            'branches' => $this->getBranchOptions($search),
            // 'departments' => $this->getDepartmentOptions($search),
        ];
    }

    /**
     * Get options for a specific field
     */
    public function getFieldOptions(string $field, ?string $search = null): array
    {
        return match($field) {
            'brands' => $this->getBrandOptions($search),
            'brandNames' => $this->getBrandNameOptions($search),
            'briboxes' => $this->getBriboxOptions($search),
            'conditions' => $this->getConditionOptions(),
            'statuses' => $this->getStatusOptions(),
            'categories' => $this->getCategoryOptions($search),
            'devices' => $this->getAvailableDeviceOptions($search),
            'users' => $this->getUserOptions($search),
            'branches' => $this->getBranchOptions($search),
            'departments' => $this->getDepartmentOptions($search),
            default => []
        };
    }

    /**
     * Get brand options (same as DeviceResource)
     */
    public function getBrandOptions(?string $search = null): array
    {
        $query = Device::query()->distinct()->select('brand');

        if ($search) {
            $query->where('brand', 'like', '%' . addslashes($search) . '%');
        }

        return $query->pluck('brand', 'brand')
            ->map(fn($brand, $key) => ['value' => $key])
            ->values()
            ->toArray();
    }

    /**
     * Get brand name options (same as DeviceResource)
     */
    public function getBrandNameOptions(?string $search = null): array
    {
        $query = Device::query()->distinct()->select('brand_name');

        if ($search) {
            $query->where('brand_name', 'like', '%' . addslashes($search) . '%');
        }

        return $query->pluck('brand_name', 'brand_name')
            ->map(fn($brandName, $key) => ['value' => $key])
            ->values()
            ->toArray();
    }

    /**
     * Get bribox options with category (same as DeviceResource)
     */
    public function getBriboxOptions(?string $search = null): array
    {
        $query = Bribox::query()->with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $escapedSearch = addslashes($search);
                $q->where('type', 'like', '%' . $escapedSearch . '%')
                  ->orWhere('bribox_id', 'like', '%' . $escapedSearch . '%')
                  ->orWhereHas('category', fn($categoryQuery) => 
                      $categoryQuery->where('category_name', 'like', '%' . $escapedSearch . '%')
                  );
            });
        }

        return $query->get()
            ->map(fn($bribox) => [
                'value' => $bribox->bribox_id,
                'label' => "{$bribox->type} (" . ($bribox->category->category_name ?? 'No Category') . ")",
            ])
            ->toArray();
    }

    /**
     * Get condition options (static)
     */
    public function getConditionOptions(): array
    {
        return collect(['Baik', 'Rusak', 'Perlu Pengecekan'])
            ->map(fn($condition) => ['value' => $condition])
            ->toArray();
    }

    /**
     * Get status options (static)
     */
    public function getStatusOptions(): array
    {
        return collect(['Digunakan', 'Tidak Digunakan', 'Cadangan'])
            ->map(fn($status) => ['value' => $status])
            ->toArray();
    }

    /**
     * Get category options
     */
    public function getCategoryOptions(?string $search = null): array
    {
        $query = BriboxesCategory::query();

        if ($search) {
            $query->where('category_name', 'like', '%' . addslashes($search) . '%');
        }

        return $query->get()
            ->map(fn($category) => [
                'category_id' => $category->getKey(),
                'label' => $category->category_name,
            ])
            ->toArray();
    }

    /**
     * Get available device options (same as DeviceAssignmentResource)
     */
    public function getAvailableDeviceOptions(?string $search = null): array
    {
        $query = Device::query()->available();

        if ($search) {
            $escapedSearch = addslashes($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('asset_code', 'like', '%' . $escapedSearch . '%')
                  ->orWhere('brand', 'like', '%' . $escapedSearch . '%')
                  ->orWhere('brand_name', 'like', '%' . $escapedSearch . '%')
                  ->orWhere('serial_number', 'like', '%' . $escapedSearch . '%');
            });
        }

        return $query->get()
            ->map(fn($device) => [
                'device_id' => $device->getKey(),
                'label' => "{$device->brand} {$device->brand_name} ({$device->serial_number})",
                'asset_code' => $device->asset_code,
                'condition' => $device->condition,
                'status' => $device->status,
            ])
            ->toArray();
    }

    /**
     * Get user options (same as DeviceAssignmentResource)
     */
    public function getUserOptions(?string $search = null): array
    {
        $query = User::query()->with('department');

        if ($search) {
            $escapedSearch = addslashes($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('name', 'like', '%' . $escapedSearch . '%')
                  ->orWhere('pn', 'like', '%' . $escapedSearch . '%')
                  ->orWhere('position', 'like', '%' . $escapedSearch . '%');
            });
        }

        return $query->get()
            ->map(fn($user) => [
                'user_id' => $user->getKey(),
                'label' => "{$user->pn} - {$user->name} (" . ($user->department->name ?? 'No Dept') . ")",
                'position' => $user->position,
            ])
            ->toArray();
    }

    /**
     * Get branch options (same as DeviceAssignmentResource)
     */
    public function getBranchOptions(?string $search = null): array
    {
        $query = Branch::query()->with('mainBranch');

        if ($search) {
            $escapedSearch = addslashes($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('unit_name', 'like', '%' . $escapedSearch . '%')
                  ->orWhere('branch_code', 'like', '%' . $escapedSearch . '%')
                  ->orWhereHas('mainBranch', fn($mainQuery) => 
                      $mainQuery->where('main_branch_name', 'like', '%' . $escapedSearch . '%')
                  );
            });
        }

        return $query->get()
            ->map(fn($branch) => [
                'branch_id' => $branch->getKey(),
                'label' => "{$branch->unit_name} (" . ($branch->mainBranch->main_branch_name ?? 'No Main Branch') . ")",
            ])
            ->toArray();
    }

    /**
     * Get department options
     */
    public function getDepartmentOptions(?string $search = null): array
    {
        $query = Department::query();

        if ($search) {
            $query->where('name', 'like', '%' . addslashes($search) . '%');
        }

        return $query->get()
            ->map(fn($department) => [
                'value' => $department->getKey(),
                'label' => $department->name,
            ])
            ->toArray();
    }

    /**
     * Get form validation rules for device
     */
    public function getDeviceValidationRules(): array
    {
        return [
            'rules' => [
                'brand' => ['required', 'string', 'max:50'],
                'brand_name' => ['required', 'string', 'max:50'],
                'serial_number' => ['required', 'string', 'max:50', 'unique:devices,serial_number'],
                'asset_code' => ['required', 'string', 'max:20', 'unique:devices,asset_code'],
                'bribox_id' => ['required', 'exists:briboxes,bribox_id'],
                'condition' => ['required', 'in:Baik,Rusak,Perlu Pengecekan'],
                'status' => ['required', 'in:Digunakan,Tidak Digunakan,Cadangan'],
                'spec1' => ['nullable', 'string', 'max:100'],
                'spec2' => ['nullable', 'string', 'max:100'],
                'spec3' => ['nullable', 'string', 'max:100'],
                'spec4' => ['nullable', 'string', 'max:100'],
                'spec5' => ['nullable', 'string', 'max:100'],
                'dev_date' => ['nullable', 'date'],
            ],
            'messages' => [
                'brand.required' => 'Brand is required',
                'brand_name.required' => 'Brand name/model is required',
                'serial_number.required' => 'Serial number is required',
                'serial_number.unique' => 'This serial number already exists',
                'asset_code.required' => 'Asset code is required',
                'asset_code.unique' => 'This asset code already exists',
                'bribox_id.required' => 'Device category (bribox) is required',
                'bribox_id.exists' => 'Selected device category is invalid',
                'condition.required' => 'Device condition is required',
                'condition.in' => 'Invalid device condition',
                'status.required' => 'Device status is required',
                'status.in' => 'Invalid device status',
            ]
        ];
    }

    /**
     * Get form validation rules for device assignment
     */
    public function getDeviceAssignmentValidationRules(): array
    {
        return [
            'rules' => [
                'device_id' => ['required', 'exists:devices,device_id'],
                'user_id' => ['required', 'exists:users,user_id'],
                'branch_id' => ['required', 'exists:branch,branch_id'],
                'assigned_date' => ['required', 'date', 'before_or_equal:today'],
                'returned_date' => ['nullable', 'date', 'after_or_equal:assigned_date'],
                'notes' => ['nullable', 'string', 'max:500'],
            ],
            'messages' => [
                'device_id.required' => 'Device is required',
                'device_id.exists' => 'Selected device is invalid',
                'user_id.required' => 'User is required',
                'user_id.exists' => 'Selected user is invalid',
                'branch_id.required' => 'Branch is required',
                'branch_id.exists' => 'Selected branch is invalid',
                'assigned_date.required' => 'Assignment date is required',
                'assigned_date.before_or_equal' => 'Assignment date cannot be in the future',
                'returned_date.after_or_equal' => 'Return date must be after assignment date',
                'notes.max' => 'Notes cannot exceed 500 characters',
            ]
        ];
    }
}
