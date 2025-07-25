<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\DeviceAssignmentService;
use App\Contracts\DeviceAssignmentRepositoryInterface;
use App\Models\AssignmentLetter;
use App\Services\MinioStorageService;
use App\Services\PdfPreviewService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DeviceAssignmentController extends Controller
{
    public function __construct(
        private DeviceAssignmentService $assignmentService,
        private DeviceAssignmentRepositoryInterface $assignmentRepository,
        private PdfPreviewService $pdfPreviewService
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
                'assetCode' => $assignment->device->asset_code,
                'brand' => $assignment->device->brand,
                'brandName' => $assignment->device->brand_name,
                'serialNumber' => $assignment->device->serial_number,
                'assignedTo' => $assignment->user->name,
                'unitName' => $assignment->branch->unit_name,
                'status' => $assignment->device->status,
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

            if ($letters->isNotEmpty()) {
                $lettersData = $letters->map(function ($letter) {
                    return [
                        'assignmentLetterId' => $letter->getKey(),
                        'assignmentType' => $letter->getAttribute('letter_type'),
                        'letterNumber' => $letter->getAttribute('letter_number'),
                        'letterDate' => $letter->getAttribute('letter_date'),
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
            $responseData = DB::transaction(function () use ($request, $validated) {
                // Create the device assignment first
                $assignmentData = $this->assignmentService->createAssignment($validated);

                // // First, Store the assignment letter file in MinIO
                // $minioStorageService = app(MinioStorageService::class);
                // $minioStorageService->storeAssignmentLetterFile(
                //     $request->file('letter_file'),
                //     $assignmentData['assignmentId'],
                //     'assignment',
                // );

                Device::where('device_id', $validated['device_id'])
                    ->update(['status' => 'Digunakan']);

                // Second, Create the assignment letter data on the database
                $letter = new AssignmentLetter([
                    'assignment_id' => $assignmentData['assignmentId'],
                    'letter_type' => 'assignment', // Default for assignment letter
                    'letter_number' => $request->input('letter_number'),
                    'letter_date' => $request->input('letter_date'),
                    'file_path' => null, // Will be set after file upload
                    'approver_id' => Auth::id(), // Get from authenticated user
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);

                // Store the letter file if present
                $path = null;
                if ($request->hasFile('letter_file') && $request->file('letter_file')->isValid()) {
                    $path = $letter->storeFile($request->file('letter_file'));
                    $letter->file_path = $path;
                }

                $letter->save();

                // Handle file upload if present
                // if ($request->hasFile('letter_file') && $request->file('letter_file')->isValid()) {
                //     $letter->storeFile($request->file('letter_file'));
                // }

                // Get the letter data with file URL
                $letterData = [
                    'assignmentLetterId' => $letter->getKey(),
                    'assignmentType' => $letter->getAttribute('letter_type'),
                    'letterNumber' => $letter->getAttribute('letter_number'),
                    'letterDate' => $letter->getAttribute('letter_date'),
                    'fileUrl' => $letter->hasFile() ? $this->pdfPreviewService->getPreviewData($letter)['previewUrl'] : null,
                ];

                // Return assignment data with the newly created letter in an array
                $responseData = array_merge($assignmentData, ['assignmentLetters' => [$letterData]]);
                return $responseData;
            }
        );

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
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'sometimes|in:Digunakan,Tidak Digunakan,Cadangan',
            'notes' => 'nullable|string|max:500',
            'returned_date' => 'nullable|date|after_or_equal:assigned_date',
        ]);

        try {
            $data = $this->assignmentService->updateAssignment($id, $request->validated());
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errorCode' => 'ERR_ASSIGNMENT_UPDATE_FAILED'
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
            // Process the device return first
            $data = $this->assignmentService->returnDevice($id, $validated);

            $minioStorageService = app(MinioStorageService::class);
            $minioStorageService->storeAssignmentLetterFile(
                $request->file('letter_file'),
                $id,
                'assignment',
            );

            // Update the device status to "Cadangan"
            $deviceId = $this->assignmentRepository->find($id)?->device_id;

            Device::where('device_id', $deviceId)
                ->update(['status' => 'Cadangan']);

            // Create the return letter

            $letter = new AssignmentLetter([
                'assignment_id' => $id,
                'letter_type' => 'return', // Return letter type
                'letter_number' => $request->input('letter_number'),
                'letter_date' => $request->input('letter_date'),
                'approver_id' => Auth::id(),
                'file_path' => null, // Will be set after file upload
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);


            // Handle file upload if present
            if ($request->hasFile('letter_file') && $request->file('letter_file')->isValid()) {
                $path = $letter->storeFile($request->file('letter_file'));
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

            // Combine return data with the newly created letter
            $responseData = array_merge($data, ['assignmentLetter' => $letterData]);

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
