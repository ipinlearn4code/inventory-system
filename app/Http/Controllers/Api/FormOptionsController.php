<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\User;
use App\Models\Branch;
use App\Models\Bribox;
use App\Models\BriboxesCategory;
use App\Models\Department;

class FormOptionsController extends Controller
{
    /**
     * Get all form options for device creation/editing
     */
    public function deviceFormOptions(Request $request)
    {
        $search = $request->input('search');
        $field = $request->input('field');

        // If specific field is requested
        if ($field) {
            return $this->getFieldOptions($field, $search);
        }

        // Return all options
        return response()->json([
            'data' => [
                'brands' => $this->getBrandOptions($search),
                'brandNames' => $this->getBrandNameOptions($search),
                'briboxes' => $this->getBriboxOptions($search),
                'conditions' => $this->getConditionOptions(),
                'statuses' => $this->getStatusOptions(),
                'categories' => $this->getCategoryOptions($search),
            ]
        ]);
    }

    /**
     * Get all form options for device assignment creation/editing
     */
    public function deviceAssignmentFormOptions(Request $request)
    {
        $search = $request->input('search');
        $field = $request->input('field');

        // If specific field is requested
        if ($field) {
            return $this->getFieldOptions($field, $search);
        }

        // Return all options
        return response()->json([
            'data' => [
                'devices' => $this->getAvailableDeviceOptions($search),
                'users' => $this->getUserOptions($search),
                'branches' => $this->getBranchOptions($search),
                'departments' => $this->getDepartmentOptions($search),
            ]
        ]);
    }

    /**
     * Get options for a specific field
     */
    private function getFieldOptions($field, $search = null)
    {
        $options = match($field) {
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

        return response()->json([
            'data' => [
                $field => $options
            ]
        ]);
    }

    /**
     * Get brand options (same as DeviceResource)
     */
    private function getBrandOptions($search = null)
    {
        $query = Device::distinct()->select('brand');
        
        if ($search) {
            $query->where('brand', 'like', "%{$search}%");
        }

        return $query->pluck('brand', 'brand')
            ->map(function ($brand, $key) {
                return [
                    'value' => $key,
                    'label' => $brand,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get brand name options (same as DeviceResource)
     */
    private function getBrandNameOptions($search = null)
    {
        $query = Device::distinct()->select('brand_name');
        
        if ($search) {
            $query->where('brand_name', 'like', "%{$search}%");
        }

        return $query->pluck('brand_name', 'brand_name')
            ->map(function ($brandName, $key) {
                return [
                    'value' => $key,
                    'label' => $brandName,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get bribox options with category (same as DeviceResource)
     */
    private function getBriboxOptions($search = null)
    {
        $query = Bribox::with('category');
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                  ->orWhere('bribox_id', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($categoryQuery) use ($search) {
                      $categoryQuery->where('category_name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->get()
            ->map(function ($bribox) {
                $categoryName = $bribox->category ? $bribox->category->category_name : 'No Category';
                return [
                    'value' => $bribox->bribox_id,
                    'label' => "{$bribox->bribox_id} - {$bribox->type} ({$categoryName})",
                    'bribox_id' => $bribox->bribox_id,
                    'type' => $bribox->type,
                    'category' => $categoryName,
                    'category_id' => $bribox->category ? $bribox->category->category_id : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get condition options (static)
     */
    private function getConditionOptions()
    {
        return [
            ['value' => 'Baik', 'label' => 'Baik'],
            ['value' => 'Rusak', 'label' => 'Rusak'],
            ['value' => 'Perlu Pengecekan', 'label' => 'Perlu Pengecekan'],
        ];
    }

    /**
     * Get status options (static)
     */
    private function getStatusOptions()
    {
        return [
            ['value' => 'Digunakan', 'label' => 'Digunakan'],
            ['value' => 'Tidak Digunakan', 'label' => 'Tidak Digunakan'],
            ['value' => 'Cadangan', 'label' => 'Cadangan'],
        ];
    }

    /**
     * Get category options
     */
    private function getCategoryOptions($search = null)
    {
        $query = BriboxesCategory::query();
        
        if ($search) {
            $query->where('category_name', 'like', "%{$search}%");
        }

        return $query->get()
            ->map(function ($category) {
                return [
                    'value' => $category->category_id,
                    'label' => $category->category_name,
                    'category_id' => $category->category_id,
                    'category_name' => $category->category_name,
                ];
            })
            ->toArray();
    }

    /**
     * Get available device options (same as DeviceAssignmentResource)
     */
    private function getAvailableDeviceOptions($search = null)
    {
        $query = Device::available();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('brand_name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        return $query->get()
            ->map(function ($device) {
                return [
                    'value' => $device->device_id,
                    'label' => "{$device->asset_code} - {$device->brand} {$device->brand_name} ({$device->serial_number})",
                    'device_id' => $device->device_id,
                    'asset_code' => $device->asset_code,
                    'brand' => $device->brand,
                    'brand_name' => $device->brand_name,
                    'serial_number' => $device->serial_number,
                    'condition' => $device->condition,
                    'status' => $device->status,
                ];
            })
            ->toArray();
    }

    /**
     * Get user options (same as DeviceAssignmentResource)
     */
    private function getUserOptions($search = null)
    {
        $query = User::with('department');
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('pn', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        return $query->get()
            ->map(function ($user) {
                $deptName = isset($user->department) ? $user->department->name : 'No Dept';
                return [
                    'value' => $user->user_id,
                    'label' => $user->pn . ' - ' . $user->name . ' (' . $deptName . ')',
                    'user_id' => $user->user_id,
                    'pn' => $user->pn,
                    'name' => $user->name,
                    'position' => $user->position,
                    'department' => $deptName,
                    'department_id' => $user->department_id,
                ];
            })
            ->toArray();
    }

    /**
     * Get branch options (same as DeviceAssignmentResource)
     */
    private function getBranchOptions($search = null)
    {
        $query = Branch::with('mainBranch');
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('unit_name', 'like', "%{$search}%")
                  ->orWhere('branch_code', 'like', "%{$search}%")
                  ->orWhereHas('mainBranch', function ($mainQuery) use ($search) {
                      $mainQuery->where('main_branch_name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->get()
            ->map(function ($branch) {
                $mainBranchName = $branch->mainBranch ? $branch->mainBranch->main_branch_name : 'No Main Branch';
                return [
                    'value' => $branch->branch_id,
                    'label' => $branch->unit_name . ' (' . $mainBranchName . ')',
                    'branch_id' => $branch->branch_id,
                    'unit_name' => $branch->unit_name,
                    'branch_code' => $branch->branch_code,
                    'main_branch' => $mainBranchName,
                    'address' => $branch->address,
                ];
            })
            ->toArray();
    }

    /**
     * Get department options
     */
    private function getDepartmentOptions($search = null)
    {
        $query = Department::query();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->get()
            ->map(function ($department) {
                return [
                    'value' => $department->department_id,
                    'label' => $department->name,
                    'department_id' => $department->department_id,
                    'name' => $department->name,
                ];
            })
            ->toArray();
    }

    /**
     * Get form validation rules for device
     */
    public function deviceValidationRules()
    {
        return response()->json([
            'data' => [
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
            ]
        ]);
    }

    /**
     * Get form validation rules for device assignment
     */
    public function deviceAssignmentValidationRules()
    {
        return response()->json([
            'data' => [
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
            ]
        ]);
    }
}
