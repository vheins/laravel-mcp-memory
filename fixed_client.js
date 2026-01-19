
import readline from "node:readline";
import fetch from "node-fetch";

// Hardcoded config
const MCP_MEMORY_URL = "http://localhost:8000/api/v1/mcp/memory";
// Ensure you use a valid token here
const MCP_MEMORY_TOKEN = "7|Mpn3qUCXvwCq7lMhU7OfufFaeQyVRzLyCcLJNIHD64c384d3";

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout,
    terminal: false
});

rl.on("line", async (line) => {
    if (!line.trim()) return;

    try {
        const msg = JSON.parse(line);

        if (msg.method === "initialize") {
            process.stdout.write(JSON.stringify({
                jsonrpc: "2.0",
                id: msg.id,
                result: {
                    protocolVersion: "2024-11-05", // Example
                    capabilities: {},
                    serverInfo: { name: "NodeProxy", version: "1.0" }
                }
            }) + "\n");
            return;
        }

        async function forwardToMCP(method, params, id) {
            const url = MCP_MEMORY_URL;
            const token = MCP_MEMORY_TOKEN;
            let result = null, error = null;
            try {
                // console.error(`Forwarding to MCP: ${method}`);
                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Content-Type": "application/json",
                        "Accept": "application/json" // FIX 1: Required for Laravel API
                    },
                    body: JSON.stringify({ jsonrpc: "2.0", method, params, id: 1 })
                });

                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    result = data.result;
                    error = data.error;
                } catch (e) {
                    // console.error("Non-JSON Response:", text);
                    error = { code: -32000, message: "Invalid JSON response from server" };
                }
            } catch (e) {
                // console.error("API ERROR:", e);
                error = { message: e.message };
            }
            process.stdout.write(JSON.stringify({
                jsonrpc: "2.0",
                id,
                ...(error ? { error } : { result })
            }) + "\n");
        }

        // FIX 2: Handle standard tools/call requests directly
        if (msg.method === "tools/call") {
            await forwardToMCP("tools/call", msg.params, msg.id);
            return;
        }

        const toolMap = {
            "memory-write": "memory-write",
            "memory-delete": "memory-delete",
            "memory-search": "memory-search",
            "memory-store": "memory-store"
        };

        if (toolMap[msg.method]) {
            // Legacy alias support
            await forwardToMCP(
                "tools/call",
                {
                    name: toolMap[msg.method],
                    arguments: msg.params
                },
                msg.id
            );
            return;
        } else {
            console.error("Unknown method:", msg.method);
        }

    } catch (e) {
        console.error("Parse error:", e);
    }
});
