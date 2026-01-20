<?php

use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Route::middleware(['auth:sanctum'])->group(function () {
    Mcp::web('memory', \App\Mcp\MemoryMcpServer::class);
    Mcp::web('memory-mcp', \App\Mcp\MemoryMcpServer::class);
});
