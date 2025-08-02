import express, { Request, Response, NextFunction } from 'express';
import cors from 'cors';
import session from 'express-session';
import bcrypt from 'bcryptjs';
import { ServerManager } from './server-manager.js';
import { ConfigLoader } from './config.js';
import { ProxyHandler } from './proxy-handler.js';

export class WebInterface {
  private app: express.Application;
  private server: any;
  private serverManager: ServerManager;
  private configLoader: ConfigLoader;
  private proxyHandler: ProxyHandler;
  private toolsCache: Map<string, any[]> = new Map();
  private passwordHash: string;

  constructor(
    serverManager: ServerManager,
    configLoader: ConfigLoader,
    proxyHandler: ProxyHandler
  ) {
    this.serverManager = serverManager;
    this.configLoader = configLoader;
    this.proxyHandler = proxyHandler;
    this.app = express();
    
    // Get password from config or environment
    const config = this.configLoader.getConfig();
    const password = process.env.MCP_PROXY_WEB_PASSWORD || 
                    (config as any).webInterface?.password || 
                    'admin';
    this.passwordHash = bcrypt.hashSync(password, 10);
    
    this.setupMiddleware();
    this.setupRoutes();
  }

  private setupMiddleware(): void {
    this.app.use(cors());
    this.app.use(express.json());
    this.app.use(express.urlencoded({ extended: true }));
    
    // Session configuration
    this.app.use(session({
      secret: process.env.MCP_PROXY_SESSION_SECRET || 'mcp-proxy-secret-change-me',
      resave: false,
      saveUninitialized: false,
      cookie: {
        secure: false, // Set to true if using HTTPS
        httpOnly: true,
        maxAge: 24 * 60 * 60 * 1000 // 24 hours
      }
    }));
  }

  private isAuthenticated(req: Request, res: Response, next: NextFunction): void {
    if ((req.session as any).authenticated) {
      next();
    } else {
      res.status(401).json({ error: 'Authentication required' });
    }
  }

  private setupRoutes(): void {
    // Serve static files (login page, etc)
    this.app.use(express.static('public'));

    // Authentication endpoints
    this.app.post('/api/login', (req: Request, res: Response) => {
      const { password } = req.body;
      if (password && bcrypt.compareSync(password, this.passwordHash)) {
        (req.session as any).authenticated = true;
        res.json({ success: true });
      } else {
        res.status(401).json({ error: 'Invalid password' });
      }
    });

    this.app.post('/api/logout', (req: Request, res: Response) => {
      req.session.destroy((err) => {
        if (err) {
          res.status(500).json({ error: 'Failed to logout' });
        } else {
          res.json({ success: true });
        }
      });
    });

    this.app.get('/api/auth/check', (req: Request, res: Response) => {
      res.json({ authenticated: !!(req.session as any).authenticated });
    });

    // Health check (no auth required)
    this.app.get('/health', (req: Request, res: Response) => {
      res.json({ status: 'ok', timestamp: new Date().toISOString() });
    });

    // Apply authentication to protected routes only
    const protectedRoutes = [
      '/api/config',
      '/api/servers',
      '/api/tools',
      '/api/sessions'
    ];
    
    this.app.use((req: Request, res: Response, next: NextFunction) => {
      // Skip auth for login/logout/auth check endpoints
      if (req.path === '/api/login' || 
          req.path === '/api/logout' || 
          req.path === '/api/auth/check') {
        return next();
      }
      
      // Check if this is a protected route
      const isProtected = protectedRoutes.some(route => req.path.startsWith(route));
      if (isProtected) {
        return this.isAuthenticated(req, res, next);
      }
      
      next();
    });

    // Get proxy configuration
    this.app.get('/api/config', (req: Request, res: Response) => {
      const config = this.configLoader.getConfig();
      res.json(config);
    });

    // Get server status
    this.app.get('/api/servers', (req: Request, res: Response) => {
      const serverStatus = this.serverManager.getServerStatus();
      res.json(serverStatus);
    });

    // Get specific server details
    this.app.get('/api/servers/:serverId', (req: Request, res: Response) => {
      const { serverId } = req.params;
      const server = this.serverManager.getServer(serverId);
      if (!server) {
        return res.status(404).json({ error: 'Server not found' });
      }
      return res.json({
        id: server.id,
        config: server.config,
        status: server.status,
        pid: server.process?.pid,
        restartCount: server.restartCount,
        startTime: server.startTime,
        lastError: server.lastError,
        tools: this.toolsCache.get(serverId) || []
      });
    });

    // Start a server
    this.app.post('/api/servers/:serverId/start', async (req: Request, res: Response) => {
      const { serverId } = req.params;
      try {
        await this.serverManager.startServer(serverId);
        return res.json({ success: true, message: `Server ${serverId} started` });
      } catch (error) {
        return res.status(500).json({ 
          success: false, 
          error: error instanceof Error ? error.message : 'Unknown error' 
        });
      }
    });

    // Stop a server
    this.app.post('/api/servers/:serverId/stop', async (req: Request, res: Response) => {
      const { serverId } = req.params;
      try {
        await this.serverManager.stopServer(serverId);
        return res.json({ success: true, message: `Server ${serverId} stopped` });
      } catch (error) {
        return res.status(500).json({ 
          success: false, 
          error: error instanceof Error ? error.message : 'Unknown error' 
        });
      }
    });

    // Restart a server
    this.app.post('/api/servers/:serverId/restart', async (req: Request, res: Response) => {
      const { serverId } = req.params;
      try {
        await this.serverManager.stopServer(serverId);
        await this.serverManager.startServer(serverId);
        return res.json({ success: true, message: `Server ${serverId} restarted` });
      } catch (error) {
        return res.status(500).json({ 
          success: false, 
          error: error instanceof Error ? error.message : 'Unknown error' 
        });
      }
    });

    // Get cached tools for all servers
    this.app.get('/api/tools', (req: Request, res: Response) => {
      const allTools: Record<string, any[]> = {};
      for (const [serverId, tools] of this.toolsCache) {
        allTools[serverId] = tools;
      }
      res.json(allTools);
    });

    // Get cached tools for a specific server
    this.app.get('/api/servers/:serverId/tools', (req: Request, res: Response) => {
      const { serverId } = req.params;
      const tools = this.toolsCache.get(serverId);
      if (!tools) {
        return res.status(404).json({ error: 'Tools not found for this server' });
      }
      return res.json(tools);
    });

    // Get active sessions
    this.app.get('/api/sessions', (req: Request, res: Response) => {
      const activeCount = this.proxyHandler.getActiveSessionCount();
      res.json({ 
        activeSessionCount: activeCount,
        // Add more session details if available
      });
    });

    // Reload configuration
    this.app.post('/api/config/reload', (req: Request, res: Response) => {
      try {
        this.configLoader.loadConfig();
        res.json({ success: true, message: 'Configuration reloaded' });
      } catch (error) {
        res.status(500).json({ 
          success: false, 
          error: error instanceof Error ? error.message : 'Unknown error' 
        });
      }
    });
  }

  public async start(): Promise<void> {
    return new Promise((resolve, reject) => {
      try {
        this.server = this.app.listen(3001, () => {
          console.log('Web interface listening on port 3001');
          resolve();
        });
      } catch (error) {
        reject(error);
      }
    });
  }

  public async stop(): Promise<void> {
    return new Promise((resolve) => {
      if (this.server) {
        this.server.close(() => {
          console.log('Web interface stopped');
          resolve();
        });
      } else {
        resolve();
      }
    });
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
}