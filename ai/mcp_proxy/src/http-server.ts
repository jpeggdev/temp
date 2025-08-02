import express, { Request, Response, NextFunction } from 'express';
import cors from 'cors';
import { v4 as uuidv4 } from 'uuid';
import { JsonRpcMessage } from './types.js';
import { ProxyHandler } from './proxy-handler.js';
import { ConfigLoader } from './config.js';

interface ExtendedRequest extends Request {
  sessionId?: string;
}

export class HttpServer {
  private app: express.Application;
  private proxyHandler: ProxyHandler;
  private configLoader: ConfigLoader;
  private server: any;

  constructor(proxyHandler: ProxyHandler, configLoader: ConfigLoader) {
    this.app = express();
    this.proxyHandler = proxyHandler;
    this.configLoader = configLoader;
    
    this.setupMiddleware();
    this.setupRoutes();
  }

  private setupMiddleware(): void {
    const config = this.configLoader.getConfig();
    
    // CORS configuration
    if (config.proxy.cors?.enabled) {
      const corsOptions: cors.CorsOptions = {
        origin: config.proxy.cors.origins || true,
        methods: ['GET', 'POST', 'OPTIONS'],
        allowedHeaders: [
          'Content-Type',
          'Accept',
          'Origin',
          'MCP-Protocol-Version',
          'Mcp-Session-Id',
          'Last-Event-ID',
          'Cache-Control'
        ],
        credentials: true
      };
      this.app.use(cors(corsOptions));
    }

    // Request parsing
    this.app.use(express.json());
    this.app.use(express.raw({ type: 'text/plain' }));

    // Request logging
    if (config.proxy.logging?.requests) {
      this.app.use(this.requestLogger.bind(this));
    }

    // Session management
    this.app.use(this.sessionManager.bind(this));

    // Security headers
    this.app.use(this.securityHeaders.bind(this));
  }

  private setupRoutes(): void {
    // Main MCP endpoint
    this.app.post('/mcp', this.handleMcpRequest.bind(this));
    this.app.get('/mcp', this.handleMcpStream.bind(this));
    
    // Health check endpoint
    this.app.get('/health', this.handleHealthCheck.bind(this));
    
    // Status endpoint
    this.app.get('/status', this.handleStatus.bind(this));
    
    // Options for CORS preflight
    this.app.options('*', (req, res) => {
      res.sendStatus(200);
    });

    // 404 handler
    this.app.use(this.notFoundHandler.bind(this));
    
    // Error handler
    this.app.use(this.errorHandler.bind(this));
  }

  private requestLogger(_req: ExtendedRequest, _res: Response, next: NextFunction): void {
    const timestamp = new Date().toISOString();
    const method = _req.method;
    const url = _req.url;
    const userAgent = _req.get('User-Agent') || 'unknown';
    const sessionId = _req.sessionId || 'no-session';
    
    console.log(`[${timestamp}] ${method} ${url} - Session: ${sessionId} - UA: ${userAgent}`);
    next();
  }

  private sessionManager(req: ExtendedRequest, _res: Response, next: NextFunction): void {
    // Get or create session ID
    let sessionId = req.get('Mcp-Session-Id');
    
    if (!sessionId) {
      sessionId = uuidv4();
      this.proxyHandler.createClientSession();
    }
    
    req.sessionId = sessionId;
    _res.set('Mcp-Session-Id', sessionId);
    
    next();
  }

  private securityHeaders(_req: Request, _res: Response, next: NextFunction): void {
    // Validate Origin header to prevent DNS rebinding attacks
    const origin = _req.get('Origin');
    const host = _req.get('Host');
    
    if (origin && host) {
      const originUrl = new URL(origin);
      if (originUrl.hostname !== 'localhost' && originUrl.hostname !== '127.0.0.1' && originUrl.hostname !== host) {
        console.warn(`Suspicious origin detected: ${origin} for host: ${host}`);
        // You may want to reject the request here depending on your security requirements
      }
    }

    // Set security headers
    _res.set({
      'X-Content-Type-Options': 'nosniff',
      'X-Frame-Options': 'DENY',
      'X-XSS-Protection': '1; mode=block',
      'Referrer-Policy': 'strict-origin-when-cross-origin'
    });
    
    next();
  }

  private async handleMcpRequest(req: ExtendedRequest, res: Response): Promise<void> {
    try {
      const acceptHeader = req.get('Accept') || '';
      // const protocolVersion = req.get('MCP-Protocol-Version');
      
      // Validate required headers
      if (!acceptHeader.includes('application/json') && !acceptHeader.includes('text/event-stream')) {
        res.status(400).json({
          error: 'Accept header must include application/json or text/event-stream'
        });
        return;
      }

      // Parse JSON-RPC message
      let message: JsonRpcMessage;
      try {
        message = typeof req.body === 'string' ? JSON.parse(req.body) : req.body;
      } catch (error) {
        res.status(400).json({
          jsonrpc: '2.0',
          id: null,
          error: {
            code: -32700,
            message: 'Parse error',
            data: 'Invalid JSON'
          }
        });
        return;
      }

      // Handle the message
      const sessionId = req.sessionId!;
      
      // Check if client wants streaming response
      if (acceptHeader.includes('text/event-stream')) {
        await this.handleStreamingResponse(req, res, sessionId, message);
      } else {
        await this.handleJsonResponse(req, res, sessionId, message);
      }
      
    } catch (error) {
      console.error('Error handling MCP request:', error);
      res.status(500).json({
        jsonrpc: '2.0',
        id: null,
        error: {
          code: -32603,
          message: 'Internal error',
          data: error instanceof Error ? error.message : String(error)
        }
      });
    }
  }

  private async handleJsonResponse(_req: ExtendedRequest, res: Response, sessionId: string, message: JsonRpcMessage): Promise<void> {
    const response = await this.proxyHandler.handleMessage(sessionId, message);
    
    res.set('Content-Type', 'application/json');
    
    if (response) {
      res.json(response);
    } else {
      // For notifications, return 202 Accepted
      res.status(202).send();
    }
  }

  private async handleStreamingResponse(req: ExtendedRequest, res: Response, sessionId: string, message: JsonRpcMessage): Promise<void> {
    // Set up SSE headers
    res.writeHead(200, {
      'Content-Type': 'text/event-stream',
      'Cache-Control': 'no-cache',
      'Connection': 'keep-alive',
      'Access-Control-Allow-Origin': req.get('Origin') || '*',
      'Access-Control-Allow-Credentials': 'true'
    });

    // Send initial connection event
    const connectionEvent = this.proxyHandler.createSSEEvent(
      { jsonrpc: '2.0', method: 'proxy/connected', params: { sessionId } },
      'connected'
    );
    res.write(this.proxyHandler.formatSSEEvent(connectionEvent));

    try {
      // Handle the message and stream the response
      const response = await this.proxyHandler.handleMessage(sessionId, message);
      
      if (response) {
        const responseEvent = this.proxyHandler.createSSEEvent(response, 'response');
        res.write(this.proxyHandler.formatSSEEvent(responseEvent));
      }

      // Set up notification forwarding for this session
      const notificationHandler = (_serverId: string, notification: JsonRpcMessage) => {
        const notificationEvent = this.proxyHandler.createSSEEvent(
          notification,
          'notification',
          uuidv4()
        );
        res.write(this.proxyHandler.formatSSEEvent(notificationEvent));
      };

      this.proxyHandler.on('notification', notificationHandler);

      // Handle client disconnect
      req.on('close', () => {
        console.log(`Client disconnected: ${sessionId}`);
        this.proxyHandler.off('notification', notificationHandler);
      });

    } catch (error) {
      const errorEvent = this.proxyHandler.createSSEEvent({
        jsonrpc: '2.0',
        id: 'method' in message && 'id' in message ? (message.id || null) : null,
        error: {
          code: -32603,
          message: 'Internal error',
          data: error instanceof Error ? error.message : String(error)
        }
      }, 'error');
      
      res.write(this.proxyHandler.formatSSEEvent(errorEvent));
    }

    // For requests, close the stream after sending the response
    if ('id' in message && 'method' in message) {
      setTimeout(() => {
        res.end();
      }, 100); // Small delay to ensure response is sent
    }
  }

  private async handleMcpStream(_req: ExtendedRequest, res: Response): Promise<void> {
    // Handle GET request for SSE stream establishment
    const acceptHeader = _req.get('Accept') || '';
    
    if (!acceptHeader.includes('text/event-stream')) {
      res.status(405).send('Method Not Allowed');
      return;
    }

    // Set up SSE headers
    res.writeHead(200, {
      'Content-Type': 'text/event-stream',
      'Cache-Control': 'no-cache',
      'Connection': 'keep-alive',
      'Access-Control-Allow-Origin': _req.get('Origin') || '*',
      'Access-Control-Allow-Credentials': 'true'
    });

    const sessionId = _req.sessionId!;

    // Send initial connection event
    const connectionEvent = this.proxyHandler.createSSEEvent(
      { jsonrpc: '2.0', method: 'proxy/connected', params: { sessionId } },
      'connected'
    );
    res.write(this.proxyHandler.formatSSEEvent(connectionEvent));

    // Send periodic heartbeat
    const heartbeatInterval = setInterval(() => {
      const heartbeatEvent = this.proxyHandler.createSSEEvent(
        { jsonrpc: '2.0', method: 'proxy/heartbeat', params: { timestamp: Date.now() } },
        'heartbeat'
      );
      res.write(this.proxyHandler.formatSSEEvent(heartbeatEvent));
    }, 30000); // 30 seconds

    // Handle client disconnect
    _req.on('close', () => {
      console.log(`SSE client disconnected: ${sessionId}`);
      clearInterval(heartbeatInterval);
      this.proxyHandler.destroyClientSession(sessionId);
    });
  }

  private handleHealthCheck(_req: Request, res: Response): void {
    const runningServers = this.proxyHandler['serverManager'].getRunningServers();
    const totalServers = this.proxyHandler['serverManager'].getAllServers().length;
    
    res.json({
      status: 'ok',
      timestamp: new Date().toISOString(),
      servers: {
        total: totalServers,
        running: runningServers.length,
        healthy: runningServers.filter(s => s.status === 'running').length
      },
      proxy: {
        uptime: process.uptime(),
        activeSessions: this.proxyHandler.getActiveSessionCount()
      }
    });
  }

  private handleStatus(req: Request, res: Response): void {
    const config = this.configLoader.getConfig();
    const serverStatus = this.proxyHandler['serverManager'].getServerStatus();
    const activeSessions = this.proxyHandler.getActiveSessions();
    
    res.json({
      proxy: {
        version: '1.0.0',
        host: config.proxy.host,
        port: config.proxy.port,
        uptime: process.uptime(),
        activeSessions: activeSessions.length
      },
      servers: serverStatus,
      sessions: activeSessions.map(session => ({
        id: session.id,
        connectedAt: session.connectedAt,
        lastActivity: session.lastActivity,
        activeRequests: session.activeRequests.size
      }))
    });
  }

  private notFoundHandler(_req: Request, res: Response): void {
    res.status(404).json({
      error: 'Not Found',
      message: `Endpoint ${_req.method} ${_req.path} not found`,
      availableEndpoints: [
        'POST /mcp - Main MCP endpoint',
        'GET /mcp - SSE stream endpoint',
        'GET /health - Health check',
        'GET /status - Detailed status'
      ]
    });
  }

  private errorHandler(error: Error, _req: Request, res: Response, _next: NextFunction): void {
    console.error('Express error:', error);
    
    res.status(500).json({
      jsonrpc: '2.0',
      id: null,
      error: {
        code: -32603,
        message: 'Internal error',
        data: error.message
      }
    });
  }

  public async start(): Promise<void> {
    const config = this.configLoader.getConfig();
    const { host, port } = config.proxy;

    return new Promise((resolve, reject) => {
      this.server = this.app.listen(port, host, () => {
        console.log(`MCP Proxy Server running at http://${host}:${port}`);
        console.log(`Available endpoints:`);
        console.log(`  POST http://${host}:${port}/mcp - Main MCP endpoint`);
        console.log(`  GET  http://${host}:${port}/mcp - SSE stream endpoint`);
        console.log(`  GET  http://${host}:${port}/health - Health check`);
        console.log(`  GET  http://${host}:${port}/status - Detailed status`);
        resolve();
      });

      this.server.on('error', (error: Error) => {
        console.error('Server error:', error);
        reject(error);
      });
    });
  }

  public async stop(): Promise<void> {
    if (!this.server) return;

    return new Promise((resolve) => {
      this.server.close(() => {
        console.log('HTTP server stopped');
        resolve();
      });
    });
  }
}