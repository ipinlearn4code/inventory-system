<?php

namespace App\Contracts;

interface FormOptionsServiceInterface
{
    /**
     * Get all form options for device creation/editing
     *
     * @param string|null $search
     * @param string|null $field
     * @return array
     */
    public function getDeviceFormOptions(?string $search = null, ?string $field = null): array;

    /**
     * Get all form options for device assignment creation/editing
     *
     * @param string|null $search
     * @param string|null $field
     * @return array
     */
    public function getDeviceAssignmentFormOptions(?string $search = null, ?string $field = null): array;

    /**
     * Get options for a specific field
     *
     * @param string $field
     * @param string|null $search
     * @return array
     */
    public function getFieldOptions(string $field, ?string $search = null): array;

    /**
     * Get brand options
     *
     * @param string|null $search
     * @return array
     */
    public function getBrandOptions(?string $search = null): array;

    /**
     * Get brand name options
     *
     * @param string|null $search
     * @return array
     */
    public function getBrandNameOptions(?string $search = null): array;

    /**
     * Get bribox options
     *
     * @param string|null $search
     * @return array
     */
    public function getBriboxOptions(?string $search = null): array;

    /**
     * Get condition options
     *
     * @return array
     */
    public function getConditionOptions(): array;

    /**
     * Get status options
     *
     * @return array
     */
    public function getStatusOptions(): array;

    /**
     * Get category options
     *
     * @param string|null $search
     * @return array
     */
    public function getCategoryOptions(?string $search = null): array;

    /**
     * Get available device options
     *
     * @param string|null $search
     * @return array
     */
    public function getAvailableDeviceOptions(?string $search = null): array;

    /**
     * Get user options
     *
     * @param string|null $search
     * @return array
     */
    public function getUserOptions(?string $search = null): array;

    /**
     * Get branch options
     *
     * @param string|null $search
     * @return array
     */
    public function getBranchOptions(?string $search = null): array;

    /**
     * Get department options
     *
     * @param string|null $search
     * @return array
     */
    public function getDepartmentOptions(?string $search = null): array;

    /**
     * Get form validation rules for device
     *
     * @return array
     */
    public function getDeviceValidationRules(): array;

    /**
     * Get form validation rules for device assignment
     *
     * @return array
     */
    public function getDeviceAssignmentValidationRules(): array;
}
