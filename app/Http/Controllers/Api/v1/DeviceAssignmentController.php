<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AssignmentLetter;
use App\Models\DeviceAssignment;
use App\Services\DeviceAssignmentService;
use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Services\PdfPreviewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'device_id' => 'sometimes|exists:devices,device_id',
            'user_id' => 'sometimes|exists:users,user_id',
            'notes' => 'sometimes|nullable|string|max:500',
            'assigned_date' => 'sometimes|date|before_or_equal:today',
            'letter_number' => 'sometimes|string|max:50',
            'letter_date' => 'sometimes|date',
            'letter_file' => 'sometimes|file|mimes:pdf|max:10240' // 10MB max size for PDF
        ]);

        try {
            return DB::transaction(function () use ($request, $id, $validated) {
                // Find the assignment
                $assignment = DeviceAssignment::with(['device', 'user.branch', 'assignmentLetters'])->findOrFail($id);
                
                $assignmentData = null;
                
                // Update assignment fields if provided
                if ($request->hasAny(['device_id', 'user_id', 'assigned_date', 'notes'])) {
                    $updateData = [];
                    
                    if ($request->has('device_id')) {
                        $updateData['device_id'] = $validated['device_id'];
                    }
                    if ($request->has('user_id')) {
                        $updateData['user_id'] = $validated['user_id'];
                    }
                    if ($request->has('assigned_date')) {
                        $updateData['assigned_date'] = $validated['assigned_date'];
                    }
                    if ($request->has('notes')) {
                        $updateData['notes'] = $validated['notes'];
                    }
                    
                    $updateData['updated_at'] = now();
                    $updateData['updated_by'] = Auth::id();
                    
                    // Update the assignment
                    DeviceAssignment::where('assignment_id', $id)->update($updateData);
                    
                    // Refresh the assignment
                    $assignment = DeviceAssignment::with(['device', 'user.branch'])->findOrFail($id);
                    
                    $assignmentData = [
                        'assignmentId' => $assignment->assignment_id,
                        'deviceId' => $assignment->device->device_id,
                        'assetCode' => $assignment->device->asset_code,
                        'brand' => $assignment->device->brand . ' ' . $assignment->device->brand_name,
                        'serialNumber' => $assignment->device->serial_number,
                        'assignedTo' => $assignment->user->name,
                        'unitName' => $assignment->user->branch->unit_name,
                        'assignedDate' => $assignment->assigned_date,
                        'returnedDate' => $assignment->returned_date,
                        'notes' => $assignment->notes,
                    ];
                }

                // Handle assignment letter updates
                if ($request->hasAny(['letter_number', 'letter_date', 'letter_file'])) {
                    // Get existing letter or create new one
                    $letter = AssignmentLetter::where('assignment_id', $id)->first();
                    
                    if (!$letter) {
                        $letter = new AssignmentLetter([
                            'assignment_id' => $id,
                            'letter_type' => 'assignment',
                            'approver_id' => Auth::id(),
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                        ]);
                    }

                    // Update letter details if provided
                    if ($request->has('letter_number')) {
                        $letter->letter_number = $request->input('letter_number');
                    }
                    if ($request->has('letter_date')) {
                        $letter->letter_date = $request->input('letter_date');
                    }
                    
                    if ($letter->exists) {
                        $letter->updated_by = Auth::id();
                        $letter->updated_at = now();
                    }

                    // Handle file upload with proper rollback support
                    if ($request->hasFile('letter_file') && $request->file('letter_file')->isValid()) {
                        if ($letter->exists) {
                            // Update existing file with rollback protection
                            $uploadResult = $letter->updateFile($request->file('letter_file'));
                            if (!$uploadResult['success']) {
                                throw new \Exception('Failed to update letter file: ' . $uploadResult['message']);
                            }
                        } else {
                            // Store new file for new letter
                            $path = $letter->storeFile($request->file('letter_file'));
                            if (!$path) {
                                throw new \Exception('Failed to store letter file');
                            }
                            $letter->file_path = $path;
                        }
                    }

                    $letter->save();

                    // Get the letter data with file URL
                    $letterData = [
                        'assignmentLetterId' => $letter->getKey(),
                        'assignmentType' => $letter->getAttribute('letter_type'),
                        'letterNumber' => $letter->getAttribute('letter_number'),
                        'letterDate' => $letter->getAttribute('letter_date'),
                        'fileUrl' => $letter->hasFile() ? $this->pdfPreviewService->getPreviewData($letter)['previewUrl'] : null,
                    ];

                    // Prepare response data
                    if (!$assignmentData) {
                        // If only letter was updated, get assignment data
                        $assignment = DeviceAssignment::with(['device', 'user.branch'])->findOrFail($id);
                        $assignmentData = [
                            'assignmentId' => $assignment->assignment_id,
                            'deviceId' => $assignment->device->device_id,
                            'assetCode' => $assignment->device->asset_code,
                            'brand' => $assignment->device->brand . ' ' . $assignment->device->brand_name,
                            'serialNumber' => $assignment->device->serial_number,
                            'assignedTo' => $assignment->user->name,
                            'unitName' => $assignment->user->branch->unit_name,
                            'assignedDate' => $assignment->assigned_date,
                            'returnedDate' => $assignment->returned_date,
                            'notes' => $assignment->notes,
                        ];
                    }
                    
                    return array_merge($assignmentData, ['assignmentLetters' => [$letterData]]);
                }

                // Return assignment data if no letter update
                if (!$assignmentData) {
                    $assignment = DeviceAssignment::with(['device', 'user.branch'])->findOrFail($id);
                    $assignmentData = [
                        'assignmentId' => $assignment->assignment_id,
                        'deviceId' => $assignment->device->device_id,
                        'assetCode' => $assignment->device->asset_code,
                        'brand' => $assignment->device->brand . ' ' . $assignment->device->brand_name,
                        'serialNumber' => $assignment->device->serial_number,
                        'assignedTo' => $assignment->user->name,
                        'unitName' => $assignment->user->branch->unit_name,
                        'assignedDate' => $assignment->assigned_date,
                        'returnedDate' => $assignment->returned_date,
                        'notes' => $assignment->notes,
                    ];
                }
                
                return $assignmentData;
            });

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
