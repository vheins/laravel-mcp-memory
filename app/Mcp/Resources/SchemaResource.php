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
            'description' => 'Memory system schema defining valid types, statuses, and scopes for all memory operations.',
            'enums' => [
                'MemoryType' => [
                    'description' => 'Categorizes the nature of the stored information.',
                    'options' => array_map(fn ($case) => [
                        'value' => $case->value,
                        'label' => method_exists($case, 'getLabel') ? $case->getLabel() : $case->name,
                        'description' => $this->getEnumDescription($case),
                    ], MemoryType::cases()),
                ],
                'MemoryStatus' => [
                    'description' => 'Tracks the lifecycle state of a memory.',
                    'options' => array_map(fn ($case) => [
                        'value' => $case->value,
                        'label' => method_exists($case, 'getLabel') ? $case->getLabel() : $case->name,
                        'description' => $this->getEnumDescription($case),
                    ], MemoryStatus::cases()),
                ],
                'MemoryScope' => [
                    'description' => 'Defines the visibility and access level of the memory.',
                    'options' => array_map(fn ($case) => [
                        'value' => $case->value,
                        'label' => method_exists($case, 'getLabel') ? $case->getLabel() : $case->name,
                        'description' => $this->getEnumDescription($case),
                    ], MemoryScope::cases()),
                ],
            ],
            'attributes' => [
                'id' => 'Unique identifier (UUID).',
                'memory_type' => 'Type of memory (entry, knowledge_base, etc.).',
                'scope_type' => 'Visibility scope (user, team, global).',
                'status' => 'Current status of the memory.',
                'title' => 'Concise summary of the memory.',
                'content' => 'The main body of information to be stored.',
                'importance' => 'Numerical score (1-10) indicating how critical the memory is.',
                'metadata' => 'Extensible JSON object for custom key-value pairs.',
                'repository' => 'Repository unique identifier or slug.',
                'organization' => 'Organization identifier.',
                'user_id' => 'System user identifier associated with the memory.',
                'created_at' => 'Timestamp of creation.',
                'updated_at' => 'Timestamp of last update.',
            ],
        ];

        return Response::text(json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
            ->withMeta(['mimeType' => 'application/json']);
    }

    protected function getEnumDescription(mixed $case): ?string
    {
        // If we decide to add getDescription() to enums later, it will pick it up automatically
        return method_exists($case, 'getDescription') ? $case->getDescription() : null;
    }
}
