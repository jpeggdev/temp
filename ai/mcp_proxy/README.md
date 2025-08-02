# MCP Proxy Server

A TypeScript-based transparent proxy server for the Model Context Protocol (MCP) that manages multiple MCP servers and provides HTTP streaming transport with a web-based management interface.

## Features

- **Transparent Proxying**: Forwards all MCP requests/responses without modification
- **Multi-Server Management**: Starts and manages multiple MCP servers using tools like `uv`, `npx`, etc.
- **HTTP Streaming Transport**: Implements the MCP HTTP transport specification with Server-Sent Events (SSE)
- **Multi-Client Support**: Handles multiple simultaneous client connections
- **Process Management**: Keeps MCP servers running with automatic restart on failure
- **Session Management**: Tracks client sessions and manages connection state
- **Health Monitoring**: Monitors server health and provides status endpoints
- **Configuration-Driven**: Configurable via JSON file with validation
- **Web Interface**: Password-protected web dashboard for monitoring and control (port 3001)
- **Tool Caching**: Caches available tools from MCP servers for instant client access
- **Auto-Start**: Configurable to start automatically on system boot
- **Graceful Shutdown**: Properly stops all managed MCP servers on proxy shutdown

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd mcp-proxy
```

2. Install dependencies:
```bash
npm install
```

3. Build the project:
```bash
npm run build
```

## Configuration

Create a `mcp_servers.json` file (see example provided) with your server configurations:

```json
{
  "servers": {
    "filesystem": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-filesystem", "/tmp"],
      "description": "File system server for /tmp directory",
      "env": {}
    },
    "git": {
      "command": "npx", 
      "args": ["-y", "@modelcontextprotocol/server-git", "--repository", "."],
      "description": "Git server for current repository",
      "env": {}
    }
  },
  "proxy": {
    "host": "localhost",
    "port": 3000,
    "cors": {
      "enabled": true,
      "origins": ["http://localhost:3001"]
    },
    "logging": {
      "level": "info",
      "requests": true
    }
  }
}
```

## Usage

### Basic Usage

```bash
# Start with default configuration
npm start

# Or use the built-in CLI
node dist/index.js

# Use custom configuration file
node dist/index.js -c /path/to/config.json
```

### Using the Startup Script

```bash
# Use the startup script for more control
./scripts/start.sh

# With custom configuration
./scripts/start.sh -c custom-config.json -e development
```

### Development Mode

```bash
# Run in development mode with auto-reload
npm run dev
```

### Web Interface

The proxy includes a web-based management interface accessible at `http://localhost:3001`. Features include:

- Real-time server status monitoring
- Start/stop/restart individual MCP servers
- View cached tools from all servers
- Session monitoring
- Password protection (default: `admin`)

To change the web interface password, set the environment variable:
```bash
export MCP_PROXY_WEB_PASSWORD=your-secure-password
```

Or add it to your configuration file:
```json
{
  "webInterface": {
    "password": "your-secure-password"
  }
}
```

### Auto-Start on System Boot

#### Linux (systemd)
```bash
# Install as systemd service
sudo ./scripts/install-systemd.sh

# Manage the service
sudo systemctl status mcp-proxy@$USER
sudo systemctl stop mcp-proxy@$USER
sudo systemctl restart mcp-proxy@$USER
```

#### macOS (launchd)
```bash
# Install as launch agent
./scripts/install-macos.sh

# Manage the service
launchctl list | grep mcp-proxy
launchctl stop com.mcp-proxy
launchctl start com.mcp-proxy
```

#### Windows
```powershell
# Run as Administrator
powershell -ExecutionPolicy Bypass -File scripts/install-windows.ps1

# Manage the service
Get-Service MCPProxyServer
Start-Service MCPProxyServer
Stop-Service MCPProxyServer
```

## API Endpoints

### Main MCP Endpoint

- **POST `/mcp`** - Send JSON-RPC messages to MCP servers
- **GET `/mcp`** - Establish SSE stream for real-time communication

#### Headers

- `Accept: application/json, text/event-stream` - Required
- `MCP-Protocol-Version: 2025-06-18` - Recommended
- `Mcp-Session-Id: <uuid>` - Optional, generated if not provided
- `Origin: <origin>` - Required for CORS validation

#### Example Request

```bash
curl -X POST http://localhost:3000/mcp \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "MCP-Protocol-Version: 2025-06-18" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "initialize",
    "params": {
      "protocolVersion": "2025-06-18",
      "capabilities": {},
      "clientInfo": {
        "name": "test-client",
        "version": "1.0.0"
      }
    }
  }'
```

#### SSE Stream Example

```bash
curl -N http://localhost:3000/mcp \
  -H "Accept: text/event-stream" \
  -H "MCP-Protocol-Version: 2025-06-18"
```

### Health Check

- **GET `/health`** - Server health and basic statistics

```json
{
  "status": "ok",
  "timestamp": "2025-01-XX",
  "servers": {
    "total": 2,
    "running": 2,
    "healthy": 2
  },
  "proxy": {
    "uptime": 3600,
    "activeSessions": 1
  }
}
```

### Detailed Status

- **GET `/status`** - Detailed server and session information

## Architecture

### Components

1. **ConfigLoader** - Loads and validates configuration
2. **ServerManager** - Manages MCP server processes
3. **ProxyHandler** - Handles request/response proxying and session management
4. **HttpServer** - HTTP server with streaming transport implementation

### Message Flow

1. Client sends HTTP request to `/mcp` endpoint
2. ProxyHandler determines target server based on routing rules
3. Request is forwarded to appropriate MCP server via stdio
4. Response is received from MCP server and forwarded back to client
5. For streaming requests, SSE connection is maintained for real-time updates

### Server Routing

The proxy uses intelligent routing to determine which MCP server should handle each request:

- **Explicit routing**: Use `serverId` parameter in request
- **Method-based routing**: Route based on method prefixes (e.g., `fs/` → filesystem server)
- **Default routing**: Use first available server

## Protocol Compliance

This proxy server implements the MCP HTTP Transport Specification:

- **JSON-RPC 2.0**: All messages use JSON-RPC 2.0 format
- **HTTP Methods**: Supports POST for requests and GET for SSE streams  
- **Headers**: Validates required headers and implements security measures
- **Streaming**: Implements Server-Sent Events for real-time communication
- **Session Management**: Tracks client sessions with unique IDs
- **Error Handling**: Proper JSON-RPC error responses

## Development

### Project Structure

```
src/
├── index.ts           # Main application entry point
├── types.ts           # TypeScript type definitions
├── config.ts          # Configuration loader
├── server-manager.ts  # MCP server process management
├── proxy-handler.ts   # Request/response proxying logic
└── http-server.ts     # HTTP server with streaming transport

scripts/
└── start.sh          # Startup script

mcp_servers.json      # Example configuration
package.json          # NPM configuration
tsconfig.json         # TypeScript configuration
```

### Building

```bash
npm run build      # Build TypeScript to JavaScript
npm run clean      # Clean build directory
npm run watch      # Build with watch mode
```

### Scripts

- `npm start` - Start the built server
- `npm run dev` - Start in development mode with ts-node
- `npm run build` - Build TypeScript to JavaScript
- `npm run clean` - Clean the dist directory
- `npm run watch` - Build with watch mode

## Security Considerations

- **Origin Validation**: Validates Origin header to prevent DNS rebinding attacks
- **Localhost Binding**: Recommended to bind only to localhost for local servers
- **CORS Configuration**: Configurable CORS settings for web client access
- **Process Isolation**: Each MCP server runs in its own process
- **Session Management**: Secure session handling with UUIDs

## Troubleshooting

### Common Issues

1. **Port already in use**: Change the port in `mcp_servers.json`
2. **Server won't start**: Check server command and arguments in configuration
3. **Connection refused**: Ensure proxy server is running and accessible
4. **CORS errors**: Configure allowed origins in `mcp_servers.json`

### Logging

The server provides detailed logging for debugging:

```bash
# Enable debug logging
export NODE_ENV=development
npm start
```

### Health Monitoring

Monitor server health via the `/health` endpoint:

```bash
curl http://localhost:3000/health
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

MIT License - see LICENSE file for details