<?php

declare(strict_types=1);

use App\Mcp\Prompts\MemoryCorePrompt;
use App\Mcp\Prompts\MemoryIndexPolicyPrompt;
use App\Mcp\Prompts\ToolUsagePrompt;
use Laravel\Mcp\Request;

test('memory core prompt returns correct content', function () {
    $prompt = new MemoryCorePrompt();
    $request = new Request([]);

    $result = $prompt->handle($request);

    expect((string) $result)->toContain('ATOMIC MEMORY')
        ->toContain('SEARCH FIRST')
        ->toContain('RESOURCE AWARENESS')
        ->toContain('SCOPE CORRECTNESS');
});

test('memory index policy prompt returns correct content', function () {
    $prompt = new MemoryIndexPolicyPrompt();
    $request = new Request([]);

    $result = $prompt->handle($request);

    expect((string) $result)->toContain('MEMORY INDEX POLICY')
        ->toContain('TITLE RULES')
        ->toContain('METADATA RULES');
});

test('tool usage prompt returns correct content', function () {
    $prompt = new ToolUsagePrompt();
    $request = new Request([]);

    $result = $prompt->handle($request);

    expect((string) $result)->toContain('TOOL USAGE GUIDELINES')
        ->toContain('memory-write')
        ->toContain('memory-update');
});
