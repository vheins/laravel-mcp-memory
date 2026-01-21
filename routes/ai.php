<?php

use App\Mcp\MemoryMcpServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Route::middleware(['auth:sanctum'])->group(function (): void {
    Mcp::web('memory', MemoryMcpServer::class);
    Mcp::web('memory-mcp', MemoryMcpServer::class);
});
