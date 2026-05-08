<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\EventController;

class TestFormWithTimestampsCommand extends Command
{
    protected $signature = 'test:form-with-timestamps';
    protected $description = 'Test form submission with timestamp data';

    public function handle()
    {
        try {
            $this->info('Testing Form Submission with Timestamps...');

            // Create a simulated request
            $request = Request::create(
                '/schedule-event',
                'POST',
                [
                    'event_title' => 'Test Event Form Submission',
                    'start_date' => '2026-03-25',
                    'end_date' => '2026-03-25',
                    'start_time' => '14:00',
                    'end_time' => '16:00',
                    'location' => 'Test Hall B',
                    'students' => [
                        [
                            'name' => 'Jane Smith',
                            'snumber' => 'TS12345',
                            'section' => 'B',
                            'rfid' => 'RF123',
                        ]
                    ]
                ]
            );

            // Simulate CSRF token
            $request->session()->put('_token', 'test-token');

            $controller = new EventController();
            $response = $controller->store($request);

            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                $this->info('✓ Form submitted successfully!');
                $this->info('Redirected to: ' . $response->getTargetUrl());
            } else {
                $this->error('✗ Unexpected response type: ' . get_class($response));
            }

        } catch (\Exception $e) {
            $this->error('✗ Error occurred:');
            $this->error($e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
            throw $e;
        }
    }
}
