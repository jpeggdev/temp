import * as fs from 'fs';
import * as path from 'path';
import { Config, ConfigSchema } from './types.js';

export class ConfigLoader {
  private static instance: ConfigLoader;
  private config: Config | null = null;
  private configPath: string;

  private constructor(configPath: string = 'mcp_servers.json') {
    this.configPath = path.resolve(configPath);
  }

  public static getInstance(configPath?: string): ConfigLoader {
    if (!ConfigLoader.instance) {
      ConfigLoader.instance = new ConfigLoader(configPath);
    }
    return ConfigLoader.instance;
  }

  public loadConfig(): Config {
    if (this.config) {
      return this.config;
    }

    try {
      const configFile = fs.readFileSync(this.configPath, 'utf-8');
      const configData = JSON.parse(configFile);
      
      // Validate configuration using Zod schema
      this.config = ConfigSchema.parse(configData);
      
      console.log(`Configuration loaded from ${this.configPath}`);
      console.log(`Found ${Object.keys(this.config.servers).length} server(s) configured`);
      
      return this.config;
    } catch (error) {
      if (error instanceof Error) {
        if (error.message.includes('ENOENT')) {
          throw new Error(`Configuration file not found: ${this.configPath}`);
        }
        if (error.message.includes('JSON')) {
          throw new Error(`Invalid JSON in configuration file: ${this.configPath}`);
        }
        throw new Error(`Configuration validation error: ${error.message}`);
      }
      throw new Error(`Unknown error loading configuration: ${error}`);
    }
  }

  public reloadConfig(): Config {
    this.config = null;
    return this.loadConfig();
  }

  public getConfig(): Config {
    if (!this.config) {
      return this.loadConfig();
    }
    return this.config;
  }

  public getServerConfig(serverId: string): Config['servers'][string] | undefined {
    const config = this.getConfig();
    return config.servers[serverId];
  }

  public getServerIds(): string[] {
    const config = this.getConfig();
    return Object.keys(config.servers);
  }

  public validateServerExists(serverId: string): boolean {
    const config = this.getConfig();
    return serverId in config.servers;
  }
}