<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp\Tools;

use Laravel\Mcp\Request;
use Exception;
use App\Mcp\Tools\WriteMemoryTool;
use App\Services\MemoryService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class WriteMemoryToolValidationTest extends TestCase
{
    public function test_it_returns_validation_errors_in_text_response(): void
    {
        // Mock the service to throw a ValidationException
        $service = $this->mock(MemoryService::class);
        $service->shouldReceive('write')
            ->once()
            ->andThrow(ValidationException::withMessages([
                'memory_type' => ['The memory type field is required.'],
            ]));

        $tool = new WriteMemoryTool();

        // Create a mock request
        $request = new Request([
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory-write',
                'arguments' => [
                    // Missing required fields
                    'title' => 'Test Memory',
                ]
            ],
            'id' => '1'
        ]);

        $response = $tool->handle($request, $service);

        // Assert response structure
        // It should NOT throw an exception, but return a Response object (or Factory)
        $this->assertNotNull($response);

        // Assert response structure
        $this->assertNotNull($response);

        // Get the content
        $content = $response->responses();

        // Assert it has content
        $this->assertNotEmpty($content);

        // Extract text content
        $textContentObj = $content[0]->content();
        $text = (string) $textContentObj;

        $this->assertJson($text);

        $errors = json_decode($text, true);
        $this->assertArrayHasKey('memory_type', $errors);
        $this->assertEquals(['The memory type field is required.'], $errors['memory_type']);
    }

    public function test_it_returns_generic_error_in_text_response(): void
    {
        // Mock the service to throw a generic Exception
        $service = $this->mock(MemoryService::class);
        $service->shouldReceive('write')
            ->once()
            ->andThrow(new Exception('Something went wrong'));

        $tool = new WriteMemoryTool();

        // Create a mock request
        $request = new Request([
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'memory-write',
                'arguments' => ['title' => 'Test']
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
        $this->assertEquals('Something went wrong', $errors['error']);
    }
}
