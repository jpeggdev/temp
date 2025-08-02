#!/usr/bin/env node

import process from 'process';
import { ConfigLoader } from './config.js';
import { ServerManager } from './server-manager.js';
import { ProxyHandler } from './proxy-handler.js';
import { HttpServer } from './http-server.js';
import { WebInterface } from './web-interface.js';

class McpProxyServer {
  private configLoader: ConfigLoader;
  private serverManager: ServerManager;
  private proxyHandler: ProxyHandler;
  private httpServer: HttpServer;
  private webInterface: WebInterface;
  private isShuttingDown = false;

  constructor(configPath?: string) {
    this.configLoader = ConfigLoader.getInstance(configPath);
    this.serverManager = new ServerManager(this.configLoader);
    this.proxyHandler = new ProxyHandler(this.serverManager, this.configLoader);
    this.httpServer = new HttpServer(this.proxyHandler, this.configLoader);
    this.webInterface = new WebInterface(this.serverManager, this.configLoader, this.proxyHandler);

    this.setupSignalHandlers();
    this.setupToolsCaching();
  }

  public async start(): Promise<void> {
    console.log('Starting MCP Proxy Server...');
    
    try {
      // Load configuration
      console.log('Loading configuration...');
      const config = this.configLoader.loadConfig();
      console.log(`Configuration loaded successfully`);
      console.log(`Proxy will run on ${config.proxy.host}:${config.proxy.port}`);

      // Start managed MCP servers
      console.log('Starting managed MCP servers...');
      await this.serverManager.startAllServers();

      // Cache tools from all running servers
      console.log('Caching tools from MCP servers...');
      await this.cacheAllServerTools();

      // Start HTTP proxy server
      console.log('Starting HTTP proxy server...');
      await this.httpServer.start();

      // Start web interface
      console.log('Starting web interface...');
      await this.webInterface.start();

      console.log('MCP Proxy Server started successfully!');
      console.log('Press Ctrl+C to gracefully shutdown');

      // Keep the process running
      this.keepAlive();

    } catch (error) {
      console.error('Failed to start MCP Proxy Server:', error);
      process.exit(1);
    }
  }

  public async stop(): Promise<void> {
    if (this.isShuttingDown) {
      console.log('Shutdown already in progress...');
      return;
    }

    this.isShuttingDown = true;
    console.log('Shutting down MCP Proxy Server...');

    try {
      // Stop web interface first
      console.log('Stopping web interface...');
      await this.webInterface.stop();

      // Stop HTTP server to prevent new connections
      console.log('Stopping HTTP server...');
      await this.httpServer.stop();

      // Stop all managed MCP servers
      console.log('Stopping managed MCP servers...');
      await this.serverManager.stopAllServers();

      console.log('MCP Proxy Server shutdown complete');
    } catch (error) {
      console.error('Error during shutdown:', error);
    }
  }

  private setupSignalHandlers(): void {
    // Graceful shutdown on SIGINT (Ctrl+C)
    process.on('SIGINT', async () => {
      console.log('\nReceived SIGINT, initiating graceful shutdown...');
      await this.stop();
      process.exit(0);
    });

    // Graceful shutdown on SIGTERM
    process.on('SIGTERM', async () => {
      console.log('Received SIGTERM, initiating graceful shutdown...');
      await this.stop();
      process.exit(0);
    });

    // Handle uncaught exceptions
    process.on('uncaughtException', (error) => {
      console.error('Uncaught Exception:', error);
      console.error('Stack:', error.stack);
      this.stop().then(() => process.exit(1));
    });

    // Handle unhandled promise rejections
    process.on('unhandledRejection', (reason, promise) => {
      console.error('Unhandled Rejection at:', promise, 'reason:', reason);
      this.stop().then(() => process.exit(1));
    });
  }

  private keepAlive(): void {
    // Set up interval to keep process alive and log stats
    const statsInterval = setInterval(() => {
      if (this.isShuttingDown) {
        clearInterval(statsInterval);
        return;
      }

      const runningServers = this.serverManager.getRunningServers();
      const activeSessions = this.proxyHandler.getActiveSessionCount();
      
      console.log(`[${new Date().toISOString()}] Status: ${runningServers.length} servers running, ${activeSessions} active sessions`);
    }, 60000); // Log every minute

    // Set up server health monitoring
    const healthInterval = setInterval(() => {
      if (this.isShuttingDown) {
        clearInterval(healthInterval);
        return;
      }

      this.performHealthCheck();
    }, 30000); // Check every 30 seconds
  }

  private performHealthCheck(): void {
    const servers = this.serverManager.getAllServers();
    for (const server of servers) {
      if (server.status === 'error' && server.restartCount < 5) {
        console.log(`Health check: attempting to restart failed server ${server.id}`);
        this.serverManager.startServer(server.id).catch(error => {
          console.error(`Failed to restart server ${server.id}:`, error);
        });
      }
    }
  }

  private setupToolsCaching(): void {
    // Listen for server started events to cache tools
    this.serverManager.on('serverStarted', async (serverId: string) => {
      console.log(`Caching tools for server ${serverId}...`);
      await this.cacheServerTools(serverId);
    });

    // Listen for server messages to detect tool list responses
    this.serverManager.on('serverMessage', (serverId: string, message: any) => {
      // Check if this is a tools/list response
      if (message.id && message.result && message.result.tools) {
        console.log(`Received tools list from server ${serverId}, caching...`);
        this.webInterface.updateToolsCache(serverId, message.result.tools);
        this.proxyHandler.updateToolsCache(serverId, message.result.tools);
      }
    });

    // Clear cache when server stops
    this.serverManager.on('serverStopped', (serverId: string) => {
      this.webInterface.clearToolsCache(serverId);
      this.proxyHandler.clearToolsCache(serverId);
    });
  }

  private async cacheServerTools(serverId: string): Promise<void> {
    const server = this.serverManager.getServer(serverId);
    if (!server || server.status !== 'running') {
      console.warn(`Cannot cache tools for server ${serverId}: not running`);
      return;
    }

    // Send tools/list request
    const toolsListRequest = {
      jsonrpc: '2.0' as const,
      id: `tools-list-${Date.now()}`,
      method: 'tools/list',
      params: {}
    };

    // Send the request
    const sent = this.serverManager.sendMessage(serverId, toolsListRequest);
    if (!sent) {
      console.error(`Failed to send tools/list request to server ${serverId}`);
      return;
    }

    // The response will be handled by the serverMessage event listener
    console.log(`Sent tools/list request to server ${serverId}`);
  }

  private async cacheAllServerTools(): Promise<void> {
    const runningServers = this.serverManager.getRunningServers();
    const cachePromises = runningServers.map(server => this.cacheServerTools(server.id));
    await Promise.allSettled(cachePromises);
    console.log(`Tools cached for ${runningServers.length} server(s)`);
  }
}

// CLI interface
function printUsage(): void {
  console.log(`
MCP Proxy Server - Transparent proxy for Model Context Protocol servers

Usage:
  mcp-proxy [options]

Options:
  -c, --config <path>    Path to configuration file (default: mcp_servers.json)
  -h, --help            Show this help message
  -v, --version         Show version information

Environment Variables:
  MCP_PROXY_CONFIG      Path to configuration file (overrides --config)
  MCP_PROXY_HOST        Host to bind to (overrides config)
  MCP_PROXY_PORT        Port to bind to (overrides config)

Examples:
  mcp-proxy                           # Use default config file
  mcp-proxy -c /path/to/config.json   # Use custom config file
  
Configuration File Format:
  See mcp_servers.json for example configuration
`);
}

function printVersion(): void {
  const packageJson = require('../package.json');
  console.log(`MCP Proxy Server v${packageJson.version}`);
}

async function main(): Promise<void> {
  const args = process.argv.slice(2);
  let configPath: string | undefined;

  // Parse command line arguments
  for (let i = 0; i < args.length; i++) {
    const arg = args[i];
    
    if (arg === '-h' || arg === '--help') {
      printUsage();
      process.exit(0);
    }
    
    if (arg === '-v' || arg === '--version') {
      printVersion();
      process.exit(0);
    }
    
    if (arg === '-c' || arg === '--config') {
      configPath = args[i + 1];
      if (!configPath) {
        console.error('Error: --config option requires a path');
        process.exit(1);
      }
      i++; // Skip next argument
    }
  }

  // Check environment variables
  configPath = process.env.MCP_PROXY_CONFIG || configPath;

  // Create and start the proxy server
  const proxyServer = new McpProxyServer(configPath);
  await proxyServer.start();
}

// Run the main function if this file is executed directly
if (require.main === module) {
  main().catch((error) => {
    console.error('Fatal error:', error);
    process.exit(1);
  });
}

export { McpProxyServer };