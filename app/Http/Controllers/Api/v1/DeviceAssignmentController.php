<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AssignmentLetter;
use App\Services\DeviceAssignmentService;
use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Contracts\InventoryLogServiceInterface;
use App\Services\PdfPreviewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeviceAssignmentController extends BaseApiController
{
    public function __construct(
        private DeviceAssignmentService $assignmentService,
        private DeviceAssignmentRepositoryInterface $assignmentRepository,
        private PdfPreviewService $pdfPreviewService,
    ) {
    }

    /**
     * Get device assignments with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'branch_id' => $request->input('branchId'),
            'active_only' => $request->input('activeOnly', false),
        ];

        $perPage = $request->input('perPage', 20);
        $assignments = $this->assignmentRepository->getPaginated($filters, $perPage);

        $data = collect($assignments->items())->map(function ($assignment) {
            return [
                'assignmentId' => $assignment->assignment_id,
                'assetCode' => $assignment->device?->asset_code ?? 'N/A',
                'brand' => $assignment->device?->brand ?? 'N/A',
                'brandName' => $assignment->device?->brand_name ?? 'N/A',
                'serialNumber' => $assignment->device?->serial_number ?? 'N/A',
                'assignedTo' => $assignment->user?->name ?? 'N/A',
                'unitName' => $assignment->branch?->unit_name ?? 'N/A',
                'status' => $assignment->device?->status ?? 'Unknown',
                'assignedDate' => $assignment->assigned_date,
                'returnedDate' => $assignment->returned_date,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'currentPage' => $assignments->currentPage(),
                'lastPage' => $assignments->lastPage(),
                'total' => $assignments->total()
            ]
        ]);
    }

    /**
     * Get assignment details by ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Get basic assignment details
            $data = $this->assignmentService->getAssignmentDetails($id);

            // Get all assignment letters
            $letters = AssignmentLetter::where('assignment_id', $id)->get();

            /**
             * Processes a collection of assignment letters and maps each letter to an array containing relevant details.
             *
             * If the collection of letters is not empty, it transforms each letter into an associative array with the following keys:
             * - 'assignmentLetterId': The unique identifier of the assignment letter.
             * - 'assignmentType': The type of the assignment letter.
             * - 'letterNumber': The number associated with the assignment letter.
             * - 'letterDate': The date of the assignment letter.
             * - 'fileUrl': The preview URL of the letter's file if available, otherwise null.
             *
             * The resulting mapped data is assigned to the 'assignmentLetters' key in the $data array.
             */

            if ($letters->isNotEmpty()) {
                $lettersData = $letters->map(function ($letter) {
                    return [
                        'assignmentLetterId' => $letter->getKey(),
                        'assignmentType' => $letter->getAttribute('letter_type'),
                        'letterNumber' => $letter->getAttribute('letter_number'),
                        'letterDate' => $letter->getAttribute('letter_date'),
                        // 'fileUrl' => $letter->hasFile() ? $this->pdfPreviewService->getPreviewData($letter)['downloadUrl'] : null
                        'fileUrl' => $letter->hasFile() ? $this->pdfPreviewService->getPreviewData($letter)['previewUrl'] : null
                    ];
                });

                $data['assignmentLetters'] = $lettersData;
            }

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'ERR_ASSIGNMENT_NOT_FOUND'
            ], 404);
        }
    }

    /**
     * Create a new device assignment
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,device_id',
            'user_id' => 'required|exists:users,user_id',
            'assigned_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
            'letter_number' => 'required|string|max:50',
            'letter_date' => 'required|date',
            'letter_file' => 'required|file|mimes:pdf|max:10240' // 10MB max size for PDF
        ]);

        try {
            $responseData = $this->assignmentService->createAssignmentWithLetter($validated, $request);

            return response()->json(['data' => $responseData], 201);
        } catch (\Exception $e) {
            $errorCode = 'ERR_ASSIGNMENT_CREATION_FAILED';
            $message = $e->getMessage();

            // Handle duplicate letter_number unique constraint
            if (
                ($e instanceof \Illuminate\Database\QueryException || $e instanceof \PDOException)
                && str_contains($message, '1062')
                && str_contains($message, 'assignment_letters_letter_number_unique')
            ) {
                $errorCode = 'ERR_DUPLICATE_LETTER_NUMBER';
                $message = 'Letter number already used. Please use a different letter number.';
            } elseif (str_contains($message, 'already assigned')) {
                $errorCode = 'ERR_DEVICE_ALREADY_ASSIGNED';
            } elseif (str_contains($message, 'already has an active assignment')) {
                $errorCode = 'ERR_USER_ALREADY_HAS_DEVICE_TYPE';
            }

            return response()->json([
                'message' => $message,
                'errorCode' => $errorCode
            ], 400);
        }
    }

    /**
     * Update a device assignment
     * 
     * This endpoint updates both the assignment details and its associated letter.
     * It supports updating assignment notes, assigned_date, and optional file uploads.
     * Uses multipart form data to handle file uploads consistently with store method.
     * 
     * Note: device_id and user_id cannot be updated via this endpoint for data integrity.
     *
     * @param Request $request The incoming request with update data
     * @param int $id The ID of the assignment to update
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'sometimes|nullable|string|max:500',
            'assigned_date' => 'sometimes|date|before_or_equal:today',
            'letter_number' => 'sometimes|string|max:50',
            'letter_date' => 'sometimes|date',
            'letter_file' => 'sometimes|file|mimes:pdf|max:10240' // 10MB max size for PDF
        ]);

        try {
            $responseData = $this->assignmentService->updateAssignmentWithLetter($id, $validated, $request);

            return response()->json(['data' => $responseData]);
        } catch (\Exception $e) {
            $errorCode = 'ERR_ASSIGNMENT_UPDATE_FAILED';
            $message = $e->getMessage();

            // Handle duplicate letter_number unique constraint
            if (
                ($e instanceof \Illuminate\Database\QueryException || $e instanceof \PDOException)
                && str_contains($message, '1062')
                && str_contains($message, 'assignment_letters_letter_number_unique')
            ) {
                $errorCode = 'ERR_DUPLICATE_LETTER_NUMBER';
                $message = 'Letter number already used. Please use a different letter number.';
            } elseif (str_contains($message, 'already assigned')) {
                $errorCode = 'ERR_DEVICE_ALREADY_ASSIGNED';
            } elseif (str_contains($message, 'already has an active assignment')) {
                $errorCode = 'ERR_USER_ALREADY_HAS_DEVICE_TYPE';
            }

            return response()->json([
                'message' => $message,
                'errorCode' => $errorCode
            ], 400);
        }
    }

    /**
     * Return a device (mark assignment as returned)
     */
    public function returnDevice(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'returned_date' => 'sometimes|date|after_or_equal:assigned_date',
            'return_notes' => 'nullable|string|max:500',
            'letter_number' => 'required|string|max:50',
            'letter_date' => 'required|date',
            'letter_file' => 'required|file|mimes:pdf|max:10240' // 10MB max size for PDF
        ]);

        try {
            $responseData = $this->assignmentService->returnDeviceWithLetter($id, $validated, $request);

            return response()->json(['data' => $responseData]);
        } catch (\Exception $e) {
            $errorCode = str_contains($e->getMessage(), 'already been returned') ? 'ERR_DEVICE_ALREADY_RETURNED' : 'ERR_RETURN_FAILED';
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => $errorCode
            ], 400);
        }
    }
}
