<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class BatchImportTest extends TestCase
{
    use WithoutMiddleware;

    public function test_batch_import_route_exists()
    {
        // Just test that the route exists and returns a response
        $response = $this->post('/event/1/batch-add-students', [
            'students' => [
                [
                    'student_name' => 'Test Student',
                    'student_number' => '12345',
                    'section' => 'A',
                    'program' => 'Test Program',
                    'rfid' => null
                ]
            ]
        ]);

        // Should get a 422 validation error since event doesn't exist, but route exists
        $response->assertStatus(422);
    }
}