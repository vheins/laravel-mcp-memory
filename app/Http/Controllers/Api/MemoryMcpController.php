<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MemoryService;
use Illuminate\Support\Facades\Auth;

class MemoryMcpController extends Controller
{
    public function __invoke(Request $request, MemoryService $service)
    {
        $request->validate([
            'jsonrpc' => 'required|in:2.0',
            'method' => 'required|string',
            'params' => 'array',
            'id' => 'required',
        ]);

        $method = $request->input('method');
        $params = $request->input('params', []);
        $id = $request->input('id');

        try {
            // Security: Enforce user context if authenticated
            \Illuminate\Support\Facades\Log::info('Auth Check: ' . (Auth::check() ? 'true' : 'false'));
            \Illuminate\Support\Facades\Log::info('Auth ID: ' . Auth::id());
            $userId = Auth::id();
            if ($userId) {
                // For writes, force the user
                if ($method === 'memory.write') {
                    $params['user_id'] = $userId;
                }

                // For searches, force the user filter
                if ($method === 'memory.search') {
                    $params['filters']['user_id'] = $userId;
                }
            }

            $result = match ($method) {
                'memory.write' => $this->handleWrite($service, $params),
                'memory.read' => $service->read($params['id']),
                'memory.search' => $service->search(
                    $params['repository'],
                    $params['query'] ?? null,
                    $params['filters'] ?? []
                ),
                default => throw new \Exception("Method not found", -32601),
            };

            return response()->json([
                'jsonrpc' => '2.0',
                'result' => $result,
                'id' => $id,
            ]);

        } catch (\Throwable $e) {
            $code = $e->getCode();
            if ($code === 0) {
                $code = -32000;
            }

            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => $code,
                    'message' => $e->getMessage(),
                ],
                'id' => $id,
            ]);
        }
    }

    protected function handleWrite(MemoryService $service, array $params)
    {
        // Infer actor from Auth or params (for agents)
        $actorId = Auth::id() ?? $params['actor_id'] ?? 'system';
        $actorType = Auth::check() ? 'human' : ($params['actor_type'] ?? 'ai');

        return $service->write($params, $actorId, $actorType);
    }
}
