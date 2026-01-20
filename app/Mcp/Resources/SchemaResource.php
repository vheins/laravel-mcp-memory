<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class SchemaResource extends Resource
{
    public function name(): string
    {
        return 'schema';
    }

    public function uri(): string
    {
        return 'schema://schema';
    }

    public function title(): string
    {
        return 'Memory Schema and Enums';
    }

    public function description(): string
    {
        return 'Information about available memory types, statuses, and scopes.';
    }

    public function handle(Request $request): Response
    {
        $schema = [
            'memory_types' => array_map(fn($case) => [
                'value' => $case->value,
                'label' => method_exists($case, 'getLabel') ? $case->getLabel() : $case->name,
            ], MemoryType::cases()),
            'memory_statuses' => array_map(fn($case) => [
                'value' => $case->value,
                'label' => method_exists($case, 'getLabel') ? $case->getLabel() : $case->name,
            ], MemoryStatus::cases()),
            'memory_scopes' => array_map(fn($case) => [
                'value' => $case->value,
                'label' => method_exists($case, 'getLabel') ? $case->getLabel() : $case->name,
            ], MemoryScope::cases()),
        ];

        return Response::text(json_encode($schema, JSON_PRETTY_PRINT))
            ->withMeta(['mimeType' => 'application/json']);
    }
}
