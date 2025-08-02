import * as childProcess from 'child_process';
import { EventEmitter } from 'events';
import { ManagedServer, JsonRpcMessage } from './types.js';
import { ConfigLoader } from './config.js';

export class ServerManager extends EventEmitter {
  private servers: Map<string, ManagedServer> = new Map();
  private configLoader: ConfigLoader;
  private readonly maxRestartAttempts = 5;
  private readonly restartDelay = 1000; // 1 second

  constructor(configLoader: ConfigLoader) {
    super();
    this.configLoader = configLoader;
  }

  public async startAllServers(): Promise<void> {
    const config = this.configLoader.getConfig();
    const serverIds = Object.keys(config.servers);

    console.log(`Starting ${serverIds.length} MCP server(s)...`);

    const startPromises = serverIds.map(serverId => this.startServer(serverId));
    await Promise.allSettled(startPromises);

    const runningServers = Array.from(this.servers.values()).filter(s => s.status === 'running');
    console.log(`Successfully started ${runningServers.length} out of ${serverIds.length} server(s)`);
  }

  public async startServer(serverId: string): Promise<ManagedServer> {
    const serverConfig = this.configLoader.getServerConfig(serverId);
    if (!serverConfig) {
      throw new Error(`Server configuration not found: ${serverId}`);
    }

    // Check if server is already running
    const existingServer = this.servers.get(serverId);
    if (existingServer && existingServer.status === 'running') {
      console.log(`Server ${serverId} is already running`);
      return existingServer;
    }

    console.log(`Starting MCP server: ${serverId}`);
    console.log(`Command: ${serverConfig.command} ${serverConfig.args.join(' ')}`);

    const server: ManagedServer = {
      id: serverId,
      config: serverConfig,
      process: null,
      status: 'starting',
      restartCount: existingServer?.restartCount || 0,
      startTime: new Date(),
    };

    this.servers.set(serverId, server);

    try {
      const childProc = childProcess.spawn(serverConfig.command, serverConfig.args, {
        stdio: ['pipe', 'pipe', 'pipe'],
        env: {
          ...process.env,
          ...serverConfig.env,
        },
        shell: false,
      });

      server.process = childProc;

      // Handle process events
      childProc.on('spawn', () => {
        console.log(`Server ${serverId} spawned with PID ${childProc.pid}`);
        server.status = 'running';
        server.startTime = new Date();
        this.emit('serverStarted', serverId);
      });

      childProc.on('error', (error) => {
        console.error(`Server ${serverId} error:`, error.message);
        server.status = 'error';
        server.lastError = error.message;
        this.emit('serverError', serverId, error);
        this.handleServerFailure(serverId);
      });

      childProc.on('exit', (code, signal) => {
        console.log(`Server ${serverId} exited with code ${code}, signal ${signal}`);
        server.status = 'stopped';
        this.emit('serverStopped', serverId, code, signal);
        
        if (code !== 0 && code !== null) {
          this.handleServerFailure(serverId);
        }
      });

      // Set up stdio handlers for JSON-RPC communication
      if (childProc.stdout) {
        childProc.stdout.setEncoding('utf8');
        let buffer = '';
        
        childProc.stdout.on('data', (data: string) => {
          buffer += data;
          
          // Process complete JSON-RPC messages
          const lines = buffer.split('\n');
          buffer = lines.pop() || ''; // Keep incomplete line in buffer
          
          for (const line of lines) {
            if (line.trim()) {
              try {
                const message = JSON.parse(line.trim()) as JsonRpcMessage;
                this.emit('serverMessage', serverId, message);
              } catch (error) {
                console.warn(`Invalid JSON from server ${serverId}:`, line);
              }
            }
          }
        });
      }

      if (childProc.stderr) {
        childProc.stderr.setEncoding('utf8');
        childProc.stderr.on('data', (data: string) => {
          console.error(`Server ${serverId} stderr:`, data.trim());
        });
      }

      return server;
    } catch (error) {
      console.error(`Failed to start server ${serverId}:`, error);
      server.status = 'error';
      server.lastError = error instanceof Error ? error.message : String(error);
      throw error;
    }
  }

  public async stopServer(serverId: string): Promise<void> {
    const server = this.servers.get(serverId);
    if (!server || !server.process) {
      console.log(`Server ${serverId} is not running`);
      return;
    }

    console.log(`Stopping MCP server: ${serverId}`);
    
    return new Promise((resolve) => {
      const timeout = setTimeout(() => {
        console.log(`Force killing server ${serverId}`);
        server.process.kill('SIGKILL');
        resolve();
      }, 5000);

      server.process.once('exit', () => {
        clearTimeout(timeout);
        server.status = 'stopped';
        resolve();
      });

      server.process.kill('SIGTERM');
    });
  }

  public async stopAllServers(): Promise<void> {
    console.log('Stopping all MCP servers...');
    const stopPromises = Array.from(this.servers.keys()).map(serverId => this.stopServer(serverId));
    await Promise.allSettled(stopPromises);
    console.log('All servers stopped');
  }

  public sendMessage(serverId: string, message: JsonRpcMessage): boolean {
    const server = this.servers.get(serverId);
    if (!server || !server.process || server.status !== 'running') {
      console.warn(`Cannot send message to server ${serverId}: not running`);
      return false;
    }

    if (server.process.stdin) {
      const messageStr = JSON.stringify(message) + '\n';
      return server.process.stdin.write(messageStr);
    }

    return false;
  }

  public getServer(serverId: string): ManagedServer | undefined {
    return this.servers.get(serverId);
  }

  public getAllServers(): ManagedServer[] {
    return Array.from(this.servers.values());
  }

  public getRunningServers(): ManagedServer[] {
    return Array.from(this.servers.values()).filter(s => s.status === 'running');
  }

  private async handleServerFailure(serverId: string): Promise<void> {
    const server = this.servers.get(serverId);
    if (!server) return;

    server.restartCount++;

    if (server.restartCount <= this.maxRestartAttempts) {
      console.log(`Attempting to restart server ${serverId} (attempt ${server.restartCount}/${this.maxRestartAttempts})`);
      
      // Wait before restarting
      await new Promise(resolve => setTimeout(resolve, this.restartDelay));
      
      try {
        await this.startServer(serverId);
      } catch (error) {
        console.error(`Failed to restart server ${serverId}:`, error);
      }
    } else {
      console.error(`Server ${serverId} has failed too many times, giving up`);
      server.status = 'error';
      server.lastError = `Max restart attempts (${this.maxRestartAttempts}) exceeded`;
    }
  }

  public getServerStatus(): Record<string, { status: string; pid?: number; restartCount: number; startTime?: Date; lastError?: string }> {
    const status: Record<string, any> = {};
    
    for (const [serverId, server] of this.servers) {
      status[serverId] = {
        status: server.status,
        pid: server.process?.pid,
        restartCount: server.restartCount,
        startTime: server.startTime,
        lastError: server.lastError,
      };
    }
    
    return status;
  }
}