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
                    'options' => array_map(fn (MemoryType $case): array => [
                        'value' => $case->value,
                        'label' => $case->getLabel() ?? ucfirst(str_replace('_', ' ', $case->value)),
                        'description' => match ($case) {
                            MemoryType::BusinessRule => 'Core logic or policy (e.g., "Discounts expire after 30 days")',
                            MemoryType::Fact => 'Immutable truth (e.g., "Server IP is 10.0.0.1")',
                            MemoryType::SystemConstraint => 'Hard technical limit (e.g., "Max upload size 10MB")',
                            MemoryType::Preference => 'User-specific setting (e.g., "Dark mode enabled")',
                            default => $case->getLabel(),
                        },
                    ], MemoryType::cases()),
                ],
                'MemoryStatus' => [
                    'description' => 'Tracks the lifecycle state of a memory.',
                    'options' => array_map(fn (MemoryStatus $case): array => [
                        'value' => $case->value,
                        'label' => $case->getLabel(),
                        'description' => match ($case) {
                            MemoryStatus::Draft => 'Initial state, pending verification.',
                            MemoryStatus::Active => 'Verified and currently in use.',
                            MemoryStatus::Deprecated => 'No longer valid but kept for history.',
                            default => $case->getLabel(),
                        },
                    ], MemoryStatus::cases()),
                ],
                'MemoryScope' => [
                    'description' => 'Defines the visibility and access level of the memory.',
                    'options' => array_map(fn (MemoryScope $case): array => [
                        'value' => $case->value,
                        'label' => $case->getLabel(),
                        'description' => match ($case) {
                            MemoryScope::System => 'Global application knowledge.',
                            MemoryScope::Organization => 'Shared across a team or tenant.',
                            MemoryScope::User => 'Private to a specific user.',
                            default => $case->getLabel(),
                        },
                    ], MemoryScope::cases()),
                ],
            ],
            'tools_compatibility' => [
                'memory_write' => [
                    'required' => ['organization', 'scope_type', 'memory_type', 'current_content'],
                    'optional' => ['title', 'status', 'importance', 'metadata', 'repository'],
                ],
            ],
            'attributes' => [
                'id' => [
                    'type' => 'uuid',
                    'description' => 'Unique identifier. Required for updates, ignored for creates.',
                ],
                'memory_type' => [
                    'type' => 'enum',
                    'options' => array_column(MemoryType::cases(), 'value'),
                    'description' => 'Categorization (see enums). Required.',
                ],
                'scope_type' => [
                    'type' => 'enum',
                    'options' => array_column(MemoryScope::cases(), 'value'),
                    'description' => 'Visibility scope. Required.',
                ],
                'status' => [
                    'type' => 'enum',
                    'options' => array_column(MemoryStatus::cases(), 'value'),
                    'description' => 'Lifecycle state. Default: draft.',
                ],
                'title' => [
                    'type' => 'string',
                    'limit' => '12 words',
                    'description' => 'Concise summary for search/indexing.',
                ],
                'current_content' => [
                    'type' => 'text',
                    'description' => 'The actual knowledge to be stored.',
                ],
                'importance' => [
                    'type' => 'integer',
                    'range' => '1-10',
                    'description' => 'Relevance score. Higher = more important.',
                ],
                'metadata' => [
                    'type' => 'json',
                    'limit' => '5 keys',
                    'description' => 'Flat key-value pairs for filtering.',
                ],
                'organization' => [
                    'type' => 'string', // slug
                    'description' => 'Organization context (slug).',
                ],
                'repository' => [
                    'type' => 'string', // slug
                    'description' => 'Repository context (slug).',
                ],
            ],
        ];

        return Response::json($schema);
    }

    public function name(): string
    {
        return 'schema';
    }

    public function title(): string
    {
        return 'Memory Schema and Enums';
    }

    public function uri(): string
    {
        return 'schema://schema';
    }


}
