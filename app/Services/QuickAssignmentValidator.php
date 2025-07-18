<?php

namespace App\Services;

use Exception;

class QuickAssignmentValidator
{
    /**
     * Validate form data before processing
     */
    public function validate(array $data): void
    {
        $this->validateRequiredFields($data);
        $this->validateApprover($data);
        $this->validateFile($data);
    }

    /**
     * Validate required fields
     */
    private function validateRequiredFields(array $data): void
    {
        $requiredFields = ['user_id', 'device_id', 'assigned_date', 'letter_number', 'letter_date'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Required field '{$field}' is missing or empty");
            }
        }
    }

    /**
     * Validate approver selection
     */
    private function validateApprover(array $data): void
    {
        if (!isset($data['approver_id']) || !$data['approver_id']) {
            throw new Exception('Approver ID is required');
        }
    }

    /**
     * Validate file upload
     */
    private function validateFile(array $data): void
    {
        if (!isset($data['file_path']) || !$data['file_path']) {
            throw new Exception('Assignment letter file is required');
        }
    }

    /**
     * Sanitize form data
     */
    public function sanitize(array $data): array
    {
        // Remove UI helper fields that shouldn't be persisted
        unset($data['is_approver']);
        
        return $data;
    }
}
