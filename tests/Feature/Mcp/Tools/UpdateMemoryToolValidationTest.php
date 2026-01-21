<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp\Tools;

use Laravel\Mcp\Request;
use Exception;
use App\Mcp\Tools\UpdateMemoryTool;
use App\Services\MemoryService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class UpdateMemoryToolValidationTest extends TestCase
{
    public function test_it_returns_validation_errors_in_text_response(): void
    {
        // Mock the service to throw a ValidationException
        $service = $this->mock(MemoryService::class);
        $service->shouldReceive('write')
            ->once()
            ->andThrow(ValidationException::withMessages([
                'status' => ['The selected status is invalid.'],
            ]));

        $tool = new UpdateMemoryTool();

        // Create a mock request
        $request = new Request([
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory-update',
                'arguments' => [
                    'id' => 'some-uuid',
                    'status' => 'invalid-status',
                ]
            ],
            'id' => '1'
        ]);

        $response = $tool->handle($request, $service);

        // Assert response structure
        $this->assertNotNull($response);

        // Get the content
        $content = $response->responses();

        // Assert it has content
        $this->assertNotEmpty($content);

        $textContentObj = $content[0]->content();
        $text = (string) $textContentObj;

        // Assert the text contains the error JSON
        $this->assertJson($text);

        $errors = json_decode($text, true);
        $this->assertArrayHasKey('status', $errors);
        $this->assertEquals(['The selected status is invalid.'], $errors['status']);
    }

    public function test_it_returns_generic_error_in_text_response(): void
    {
        // Mock the service to throw a generic Exception
        $service = $this->mock(MemoryService::class);
        $service->shouldReceive('write')
            ->once()
            ->andThrow(new Exception('Update failed'));

        $tool = new UpdateMemoryTool();

        // Create a mock request
        $request = new Request([
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory-update',
                'arguments' => ['id' => '123']
            ],
            'id' => '1'
        ]);

        $response = $tool->handle($request, $service);

        // Assert response structure
        $this->assertNotNull($response);

        // Get the content
        $content = $response->responses();
        $this->assertNotEmpty($content);

        // Extract text content
        $textContentObj = $content[0]->content();
        $text = (string) $textContentObj;

        $this->assertJson($text);

        $errors = json_decode($text, true);
        $this->assertArrayHasKey('error', $errors);
        $this->assertEquals('Update failed', $errors['error']);
    }
}
