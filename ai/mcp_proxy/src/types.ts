import { z } from 'zod';

// Zod schemas for configuration validation
export const ServerConfigSchema = z.object({
  command: z.string(),
  args: z.array(z.string()),
  description: z.string().optional(),
  env: z.record(z.string()).optional(),
});

export const CorsConfigSchema = z.object({
  enabled: z.boolean(),
  origins: z.array(z.string()).optional(),
});

export const LoggingConfigSchema = z.object({
  level: z.enum(['debug', 'info', 'warn', 'error']),
  requests: z.boolean().optional(),
  responses: z.boolean().optional(),
});

export const ProxyConfigSchema = z.object({
  host: z.string().default('localhost'),
  port: z.number().min(1).max(65535).default(3000),
  cors: CorsConfigSchema.optional(),
  logging: LoggingConfigSchema.optional(),
});

export const ConfigSchema = z.object({
  servers: z.record(ServerConfigSchema),
  proxy: ProxyConfigSchema,
});

// TypeScript types derived from Zod schemas
export type ServerConfig = z.infer<typeof ServerConfigSchema>;
export type CorsConfig = z.infer<typeof CorsConfigSchema>;
export type LoggingConfig = z.infer<typeof LoggingConfigSchema>;
export type ProxyConfig = z.infer<typeof ProxyConfigSchema>;
export type Config = z.infer<typeof ConfigSchema>;

// JSON-RPC 2.0 message types
export interface JsonRpcRequest {
  jsonrpc: '2.0';
  id: string | number | null | undefined;
  method: string;
  params?: any;
}

export interface JsonRpcResponse {
  jsonrpc: '2.0';
  id: string | number | null | undefined;
  result?: any;
  error?: JsonRpcError;
}

export interface JsonRpcNotification {
  jsonrpc: '2.0';
  method: string;
  params?: any;
}

export interface JsonRpcError {
  code: number;
  message: string;
  data?: any;
}

export type JsonRpcMessage = JsonRpcRequest | JsonRpcResponse | JsonRpcNotification;

// MCP server process information
export interface ManagedServer {
  id: string;
  config: ServerConfig;
  process: any; // child_process.ChildProcess
  status: 'starting' | 'running' | 'stopped' | 'error';
  port?: number;
  lastError?: string;
  restartCount: number;
  startTime?: Date;
}

// Client session information
export interface ClientSession {
  id: string;
  connectedAt: Date;
  lastActivity: Date;
  activeRequests: Map<string | number, {
    serverId: string;
    timestamp: Date;
  }>;
}

// SSE event structure
export interface SSEEvent {
  id?: string;
  event?: string;
  data: string;
  retry?: number;
}