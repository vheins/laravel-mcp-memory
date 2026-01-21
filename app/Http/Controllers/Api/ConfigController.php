<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConfigurationResource;
use App\Models\Configuration;
use App\Services\ConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConfigController extends Controller
{
    public function __construct(protected ConfigService $configService) {}

    /**
     * Public fetch endpoint.
     */
    public function fetch(): JsonResponse
    {
        // Public configs only
        $configs = Configuration::query()->where('is_public', true)->get();

        $mapped = $configs->mapWithKeys(fn ($item) => [$item->key => $item->value]);

        return response()->json([
            'data' => $mapped,
        ]);
    }

    /**
     * list all configurations (Admin only usually, but for now restricted via middleware if needed).
     */
    public function index(): AnonymousResourceCollection
    {
        $configs = Configuration::query()->latest()->paginate();

        return ConfigurationResource::collection($configs);
    }

    /**
     * Show a configuration.
     */
    public function show(Configuration $configuration): ConfigurationResource
    {
        return new ConfigurationResource($configuration);
    }

    /**
     * Update a configuration.
     */
    public function update(Request $request, Configuration $configuration): ConfigurationResource
    {
        $request->validate([
            'value' => ['nullable'], // Validation relies on casting preparation basically
        ]);

        // Using service to ensure consistency if we add more logic there
        // But Controller can also call model update directly which fires events.
        // Let's use service set method for 'value' update specifically if simple.
        // However, standard update might cover other fields.
        // The requirement said "Update DB -> Hapus Cache Key", model handles that.

        // Let's stick to standard update for full crud, use service for specific value-setting business logic if distinct.
        // System Config usually updates value.

        $configuration->update($request->only(['value', 'is_public', 'group']));

        return new ConfigurationResource($configuration);
    }
}
