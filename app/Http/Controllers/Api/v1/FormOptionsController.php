<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Contracts\FormOptionsServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FormOptionsController extends BaseApiController
{
    private FormOptionsServiceInterface $formOptionsService;

    public function __construct(FormOptionsServiceInterface $formOptionsService)
    {
        $this->formOptionsService = $formOptionsService;
    }

    /**
     * Get all form options for device creation/editing
     */
    public function deviceFormOptions(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $field = $request->input('field');

            $data = $this->formOptionsService->getDeviceFormOptions($search, $field);

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get device form options', 'ERR_DEVICE_FORM_OPTIONS_FAILED', 500);
        }
    }

    /**
     * Get all form options for device assignment creation/editing
     */
    public function deviceAssignmentFormOptions(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $field = $request->input('field');

            $data = $this->formOptionsService->getDeviceAssignmentFormOptions($search, $field);

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get device assignment form options', 'ERR_ASSIGNMENT_FORM_OPTIONS_FAILED', 500);
        }
    }

    /**
     * Get form validation rules for device
     *
     * @return JsonResponse
     */
    public function deviceValidationRules(): JsonResponse
    {
        try {
            $data = $this->formOptionsService->getDeviceValidationRules();

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get device validation rules', 'ERR_DEVICE_VALIDATION_RULES_FAILED', 500);
        }
    }

    /**
     * Get form validation rules for device assignment
     *
     * @return JsonResponse
     */
    public function deviceAssignmentValidationRules(): JsonResponse
    {
        try {
            $data = $this->formOptionsService->getDeviceAssignmentValidationRules();

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get device assignment validation rules', 'ERR_ASSIGNMENT_VALIDATION_RULES_FAILED', 500);
        }
    }

    /**
     * Get options for a specific field type
     * This endpoint can be used to get options for any form field
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFieldOptions(Request $request): JsonResponse
    {
        try {
            $field = $request->input('field');
            $search = $request->input('search');

            if (!$field) {
                return $this->errorResponse('Field parameter is required', 'ERR_FIELD_REQUIRED', 400);
            }

            $options = $this->formOptionsService->getFieldOptions($field, $search);

            return $this->successResponse([$field => $options]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get field options', 'ERR_FIELD_OPTIONS_FAILED', 500);
        }
    }
}
