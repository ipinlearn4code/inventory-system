<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\User;
use App\Models\DeviceAssignment;
use App\Models\AssignmentLetter;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AssignmentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $device;
    protected $branch;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup storage disk for testing
        Storage::fake('minio');
        
        // Create test data
        $this->branch = Branch::factory()->create();
        $this->department = Department::factory()->create();
        
        $this->user = User::factory()->create([
            'branch_id' => $this->branch->branch_id,
            'department_id' => $this->department->department_id,
        ]);
        
        $this->device = Device::factory()->create([
            'status' => 'Cadangan'
        ]);
        
        // Authenticate user
        Auth::login($this->user);
    }

    /** @test */
    public function it_can_create_assignment_with_letter()
    {
        $file = UploadedFile::fake()->create('assignment_letter.pdf', 1000, 'application/pdf');
        
        $assignmentData = [
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id,
            'assigned_date' => now()->format('Y-m-d'),
            'notes' => 'Test assignment notes',
            'letter_number' => 'ASG/2025/001',
            'letter_date' => now()->format('Y-m-d'),
            'letter_file' => $file,
        ];

        $response = $this->postJson('/api/v1/test-assignments', $assignmentData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'assignmentId',
                        'assetCode',
                        'assignedTo',
                        'assignmentLetters' => [
                            '*' => [
                                'assignmentLetterId',
                                'assignmentType',
                                'letterNumber',
                                'letterDate',
                                'fileUrl'
                            ]
                        ]
                    ]
                ]);

        // Verify database records
        $this->assertDatabaseHas('device_assignments', [
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id,
            'notes' => 'Test assignment notes'
        ]);

        $this->assertDatabaseHas('assignment_letters', [
            'letter_number' => 'ASG/2025/001',
            'letter_type' => 'assignment'
        ]);

        // Verify device status updated
        $this->assertDatabaseHas('devices', [
            'device_id' => $this->device->device_id,
            'status' => 'Digunakan'
        ]);
    }

    /** @test */
    public function it_can_update_assignment_notes_and_date()
    {
        // Create an assignment first
        $assignment = DeviceAssignment::factory()->create([
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id,
            'notes' => 'Original notes'
        ]);

        $updateData = [
            'notes' => 'Updated assignment notes',
            'assigned_date' => now()->subDay()->format('Y-m-d'),
        ];

        $response = $this->patchJson("/api/v1/test-assignments/{$assignment->assignment_id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'assignmentId',
                        'notes'
                    ]
                ]);

        // Verify database was updated
        $this->assertDatabaseHas('device_assignments', [
            'assignment_id' => $assignment->assignment_id,
            'notes' => 'Updated assignment notes'
        ]);
    }

    /** @test */
    public function it_can_update_assignment_letter_details()
    {
        // Create assignment with letter
        $assignment = DeviceAssignment::factory()->create([
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id
        ]);

        $letter = AssignmentLetter::factory()->create([
            'assignment_id' => $assignment->assignment_id,
            'letter_number' => 'ASG/2025/001',
            'letter_type' => 'assignment'
        ]);

        $updateData = [
            'letter_number' => 'ASG/2025/002',
            'letter_date' => now()->format('Y-m-d'),
        ];

        $response = $this->patchJson("/api/v1/test-assignments/{$assignment->assignment_id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'assignmentLetters' => [
                            '*' => [
                                'letterNumber',
                                'letterDate'
                            ]
                        ]
                    ]
                ]);

        // Verify letter was updated
        $this->assertDatabaseHas('assignment_letters', [
            'assignment_id' => $assignment->assignment_id,
            'letter_number' => 'ASG/2025/002'
        ]);
    }

    /** @test */
    public function it_can_replace_assignment_letter_file()
    {
        // Create assignment with existing letter and file
        $assignment = DeviceAssignment::factory()->create([
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id
        ]);

        $letter = AssignmentLetter::factory()->create([
            'assignment_id' => $assignment->assignment_id,
            'letter_number' => 'ASG/2025/001',
            'letter_type' => 'assignment',
            'file_path' => 'old/path/old_file.pdf'
        ]);

        // Create new file for upload
        $newFile = UploadedFile::fake()->create('new_assignment_letter.pdf', 1500, 'application/pdf');

        $updateData = [
            'letter_file' => $newFile,
        ];

        $response = $this->patchJson("/api/v1/test-assignments/{$assignment->assignment_id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'assignmentLetters' => [
                            '*' => [
                                'fileUrl'
                            ]
                        ]
                    ]
                ]);

        // Verify letter file_path was updated in database
        $letter->refresh();
        $this->assertNotEquals('old/path/old_file.pdf', $letter->file_path);
        $this->assertNotNull($letter->file_path);
    }

    /** @test */
    public function it_can_update_both_assignment_and_letter_data()
    {
        // Create assignment with letter
        $assignment = DeviceAssignment::factory()->create([
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id,
            'notes' => 'Original notes'
        ]);

        $letter = AssignmentLetter::factory()->create([
            'assignment_id' => $assignment->assignment_id,
            'letter_number' => 'ASG/2025/001'
        ]);

        $file = UploadedFile::fake()->create('updated_letter.pdf', 1200, 'application/pdf');

        $updateData = [
            'notes' => 'Updated notes from test',
            'assigned_date' => now()->subDays(2)->format('Y-m-d'),
            'letter_number' => 'ASG/2025/999',
            'letter_date' => now()->format('Y-m-d'),
            'letter_file' => $file,
        ];

        $response = $this->patchJson("/api/v1/test-assignments/{$assignment->assignment_id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'assignmentId',
                        'notes',
                        'assignmentLetters'
                    ]
                ]);

        // Verify both assignment and letter were updated
        $this->assertDatabaseHas('device_assignments', [
            'assignment_id' => $assignment->assignment_id,
            'notes' => 'Updated notes from test'
        ]);

        $this->assertDatabaseHas('assignment_letters', [
            'assignment_id' => $assignment->assignment_id,
            'letter_number' => 'ASG/2025/999'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_creation()
    {
        $response = $this->postJson('/api/v1/test-assignments', [
            // Missing required fields
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'device_id',
                    'user_id',
                    'assigned_date',
                    'letter_number',
                    'letter_date',
                    'letter_file'
                ]);
    }

    /** @test */
    public function it_rejects_device_id_and_user_id_updates()
    {
        $assignment = DeviceAssignment::factory()->create([
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id
        ]);

        $anotherDevice = Device::factory()->create();
        $anotherUser = User::factory()->create();

        $updateData = [
            'device_id' => $anotherDevice->device_id,
            'user_id' => $anotherUser->user_id,
            'notes' => 'Trying to update device and user'
        ];

        $response = $this->patchJson("/api/v1/test-assignments/{$assignment->assignment_id}", $updateData);

        // Should succeed but device_id and user_id should be ignored
        $response->assertStatus(200);

        // Verify original device_id and user_id remain unchanged
        $this->assertDatabaseHas('device_assignments', [
            'assignment_id' => $assignment->assignment_id,
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id,
            'notes' => 'Trying to update device and user'
        ]);
    }

    /** @test */
    public function it_handles_duplicate_letter_number_gracefully()
    {
        // Create existing assignment with letter
        $existingAssignment = DeviceAssignment::factory()->create();
        AssignmentLetter::factory()->create([
            'assignment_id' => $existingAssignment->assignment_id,
            'letter_number' => 'DUPLICATE/001'
        ]);

        // Try to create new assignment with same letter number
        $file = UploadedFile::fake()->create('assignment.pdf', 1000, 'application/pdf');

        $assignmentData = [
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id,
            'assigned_date' => now()->format('Y-m-d'),
            'letter_number' => 'DUPLICATE/001', // Same as existing
            'letter_date' => now()->format('Y-m-d'),
            'letter_file' => $file,
        ];

        $response = $this->postJson('/api/v1/test-assignments', $assignmentData);

        $response->assertStatus(400)
                ->assertJson([
                    'errorCode' => 'ERR_DUPLICATE_LETTER_NUMBER'
                ]);
    }

    /** @test */
    public function it_validates_file_type_and_size()
    {
        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('document.txt', 1000, 'text/plain');

        $assignmentData = [
            'device_id' => $this->device->device_id,
            'user_id' => $this->user->user_id,
            'assigned_date' => now()->format('Y-m-d'),
            'letter_number' => 'ASG/2025/001',
            'letter_date' => now()->format('Y-m-d'),
            'letter_file' => $invalidFile,
        ];

        $response = $this->postJson('/api/v1/test-assignments', $assignmentData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['letter_file']);

        // Test oversized file
        $oversizedFile = UploadedFile::fake()->create('huge.pdf', 15000, 'application/pdf'); // 15MB

        $assignmentData['letter_file'] = $oversizedFile;

        $response = $this->postJson('/api/v1/test-assignments', $assignmentData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['letter_file']);
    }
}
