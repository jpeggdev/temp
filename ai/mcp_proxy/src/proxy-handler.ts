import { EventEmitter } from 'events';
import { v4 as uuidv4 } from 'uuid';
import { JsonRpcMessage, JsonRpcRequest, JsonRpcResponse, JsonRpcNotification, SSEEvent, ClientSession } from './types.js';
import { ServerManager } from './server-manager.js';
import { ConfigLoader } from './config.js';

export class ProxyHandler extends EventEmitter {
  private serverManager: ServerManager;
  private _configLoader: ConfigLoader;
  private clientSessions: Map<string, ClientSession> = new Map();
  private pendingRequests: Map<string | number, {
    clientSessionId: string;
    serverId: string;
    timestamp: Date;
    resolve: (value: JsonRpcResponse) => void;
    reject: (error: Error) => void;
  }> = new Map();
  private toolsCache: Map<string, any[]> = new Map();

  constructor(serverManager: ServerManager, _configLoader: ConfigLoader) {
    super();
    this.serverManager = serverManager;
    this._configLoader = _configLoader;

    // Listen for messages from managed servers
    this.serverManager.on('serverMessage', this.handleServerMessage.bind(this));
  }

  public createClientSession(): ClientSession {
    const sessionId = uuidv4();
    const session: ClientSession = {
      id: sessionId,
      connectedAt: new Date(),
      lastActivity: new Date(),
      activeRequests: new Map(),
    };

    this.clientSessions.set(sessionId, session);
    console.log(`Created client session: ${sessionId}`);
    return session;
  }

  public destroyClientSession(sessionId: string): void {
    const session = this.clientSessions.get(sessionId);
    if (session) {
      // Cancel any pending requests for this session
      for (const [requestId, pendingRequest] of this.pendingRequests) {
        if (pendingRequest.clientSessionId === sessionId) {
          pendingRequest.reject(new Error('Client session terminated'));
          this.pendingRequests.delete(requestId);
        }
      }

      this.clientSessions.delete(sessionId);
      console.log(`Destroyed client session: ${sessionId}`);
    }
  }

  public async handleMessage(sessionId: string, message: JsonRpcMessage): Promise<JsonRpcResponse | void> {
    const session = this.clientSessions.get(sessionId);
    if (!session) {
      throw new Error(`Invalid session: ${sessionId}`);
    }

    session.lastActivity = new Date();

    // Handle different message types
    if (this.isRequest(message)) {
      return this.handleRequest(sessionId, message);
    } else if (this.isNotification(message)) {
      return this.handleNotification(sessionId, message);
    } else if (this.isResponse(message)) {
      return this.handleResponse(sessionId, message);
    }

    throw new Error('Invalid JSON-RPC message format');
  }

  private async handleRequest(sessionId: string, request: JsonRpcRequest): Promise<JsonRpcResponse> {
    const session = this.clientSessions.get(sessionId)!;

    // Special handling for proxy-specific methods
    if (request.method === 'proxy/listServers') {
      return this.handleListServers(request);
    }

    if (request.method === 'proxy/getServerStatus') {
      return this.handleGetServerStatus(request);
    }

    // Handle tools/list requests with cached data
    if (request.method === 'tools/list') {
      return this.handleToolsList(request);
    }

    // Route request to appropriate server
    const serverId = this.determineTargetServer(request);
    if (!serverId) {
      return {
        jsonrpc: '2.0',
        id: request.id,
        error: {
          code: -32601,
          message: 'Method not found or no available server',
          data: { method: request.method }
        }
      };
    }

    const server = this.serverManager.getServer(serverId);
    if (!server || server.status !== 'running') {
      return {
        jsonrpc: '2.0',
        id: request.id,
        error: {
          code: -32000,
          message: 'Server not available',
          data: { serverId, status: server?.status }
        }
      };
    }

    // Forward request to server and wait for response
    return new Promise((resolve, reject) => {
      if (request.id === null || request.id === undefined) {
        reject(new Error('Request ID is required'));
        return;
      }

      const timeoutId = setTimeout(() => {
        this.pendingRequests.delete(request.id!);
        reject(new Error('Request timeout'));
      }, 30000); // 30 second timeout

      this.pendingRequests.set(request.id, {
        clientSessionId: sessionId,
        serverId,
        timestamp: new Date(),
        resolve: (response) => {
          clearTimeout(timeoutId);
          resolve(response);
        },
        reject: (error) => {
          clearTimeout(timeoutId);
          reject(error);
        }
      });

      session.activeRequests.set(request.id, {
        serverId,
        timestamp: new Date()
      });

      // Send request to server
      const sent = this.serverManager.sendMessage(serverId, request);
      if (!sent) {
        this.pendingRequests.delete(request.id);
        session.activeRequests.delete(request.id);
        clearTimeout(timeoutId);
        reject(new Error(`Failed to send request to server ${serverId}`));
      }
    });
  }

  private async handleNotification(_sessionId: string, notification: JsonRpcNotification): Promise<void> {
    // Broadcast notification to all servers or specific server based on method
    const serverId = this.determineTargetServer(notification);
    
    if (serverId) {
      const server = this.serverManager.getServer(serverId);
      if (server && server.status === 'running') {
        this.serverManager.sendMessage(serverId, notification);
      }
    } else {
      // Broadcast to all running servers
      const runningServers = this.serverManager.getRunningServers();
      for (const server of runningServers) {
        this.serverManager.sendMessage(server.id, notification);
      }
    }
  }

  private async handleResponse(_sessionId: string, _response: JsonRpcResponse): Promise<void> {
    // This would typically be used if the proxy needs to send responses back to servers
    // For now, this is not needed as we're primarily proxying client->server communication
    console.warn('Received response from client, but proxy does not expect responses from clients');
  }

  private handleServerMessage(serverId: string, message: JsonRpcMessage): void {
    if (this.isResponse(message)) {
      // Find pending request and resolve it
      if (message.id !== null && message.id !== undefined) {
        const pendingRequest = this.pendingRequests.get(message.id);
        if (pendingRequest) {
          const session = this.clientSessions.get(pendingRequest.clientSessionId);
          if (session) {
            session.activeRequests.delete(message.id);
          }
          
          this.pendingRequests.delete(message.id);
          pendingRequest.resolve(message);
        } else {
          console.warn(`Received response for unknown request ID: ${message.id}`);
        }
      }
    } else if (this.isNotification(message)) {
      // Broadcast notification to all connected clients
      this.emit('notification', serverId, message);
    } else if (this.isRequest(message)) {
      // Handle server-initiated requests (rare)
      this.emit('serverRequest', serverId, message);
    }
  }

  private determineTargetServer(message: JsonRpcMessage): string | null {
    // For now, use simple routing based on method prefixes
    // In a more sophisticated implementation, this could use:
    // - Method-to-server mapping
    // - Load balancing
    // - Health checks
    // - Client preferences

    const method = 'method' in message ? message.method : '';
    
    // Check for explicit server routing
    if ('params' in message && message.params && typeof message.params === 'object' && 'serverId' in message.params) {
      return message.params.serverId as string;
    }

    // Method-based routing
    if (method.startsWith('fs/') || method.includes('filesystem')) {
      return this.findServerByType('filesystem');
    }
    
    if (method.startsWith('git/') || method.includes('git')) {
      return this.findServerByType('git');
    }
    
    if (method.startsWith('sqlite/') || method.includes('sqlite')) {
      return this.findServerByType('sqlite');
    }

    // Default: use first available server
    const runningServers = this.serverManager.getRunningServers();
    return runningServers.length > 0 ? runningServers[0].id : null;
  }

  private findServerByType(type: string): string | null {
    const runningServers = this.serverManager.getRunningServers();
    const server = runningServers.find(s => s.id.includes(type));
    return server?.id || null;
  }

  private handleListServers(request: JsonRpcRequest): JsonRpcResponse {
    const servers = this.serverManager.getAllServers().map(server => ({
      id: server.id,
      description: server.config.description,
      status: server.status,
      pid: server.process?.pid,
      restartCount: server.restartCount,
      startTime: server.startTime,
    }));

    return {
      jsonrpc: '2.0',
      id: request.id || null,
      result: { servers }
    };
  }

  private handleGetServerStatus(request: JsonRpcRequest): JsonRpcResponse {
    const status = this.serverManager.getServerStatus();
    const sessionCount = this.clientSessions.size;
    const activeRequestCount = this.pendingRequests.size;

    return {
      jsonrpc: '2.0',
      id: request.id || null,
      result: {
        servers: status,
        proxy: {
          activeSessions: sessionCount,
          activeRequests: activeRequestCount,
          uptime: process.uptime()
        }
      }
    };
  }

  private isRequest(message: JsonRpcMessage): message is JsonRpcRequest {
    return 'method' in message && 'id' in message;
  }

  private isNotification(message: JsonRpcMessage): message is JsonRpcNotification {
    return 'method' in message && !('id' in message);
  }

  private isResponse(message: JsonRpcMessage): message is JsonRpcResponse {
    return 'id' in message && !('method' in message);
  }

  public createSSEEvent(data: JsonRpcMessage, eventType?: string, eventId?: string): SSEEvent {
    const event: SSEEvent = {
      data: JSON.stringify(data)
    };
    
    if (eventId) {
      event.id = eventId;
    }
    
    if (eventType) {
      event.event = eventType;
    }
    
    return event;
  }

  public formatSSEEvent(event: SSEEvent): string {
    let formatted = '';
    
    if (event.id) {
      formatted += `id: ${event.id}\n`;
    }
    
    if (event.event) {
      formatted += `event: ${event.event}\n`;
    }
    
    if (event.retry) {
      formatted += `retry: ${event.retry}\n`;
    }
    
    // Handle multi-line data
    const dataLines = event.data.split('\n');
    for (const line of dataLines) {
      formatted += `data: ${line}\n`;
    }
    
    formatted += '\n';
    return formatted;
  }

  public getActiveSessionCount(): number {
    return this.clientSessions.size;
  }

  public updateToolsCache(serverId: string, tools: any[]): void {
    this.toolsCache.set(serverId, tools);
  }

  public clearToolsCache(serverId?: string): void {
    if (serverId) {
      this.toolsCache.delete(serverId);
    } else {
      this.toolsCache.clear();
    }
  }

  private handleToolsList(request: JsonRpcRequest): JsonRpcResponse {
    // Combine tools from all cached servers
    const allTools: any[] = [];
    for (const [serverId, tools] of this.toolsCache) {
      // Add server information to each tool
      const toolsWithServerId = tools.map(tool => ({
        ...tool,
        _serverId: serverId // Internal metadata for routing
      }));
      allTools.push(...toolsWithServerId);
    }

    return {
      jsonrpc: '2.0',
      id: request.id,
      result: {
        tools: allTools
      }
    };
  }

  public getActiveSessions(): ClientSession[] {
    return Array.from(this.clientSessions.values());
  }
}