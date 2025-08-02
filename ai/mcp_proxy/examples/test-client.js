#!/usr/bin/env node

/**
 * Simple test client for MCP Proxy Server
 * 
 * This client demonstrates how to connect to the MCP proxy server
 * and send JSON-RPC requests to managed MCP servers.
 */

const http = require('http');

class McpTestClient {
  constructor(host = 'localhost', port = 3000) {
    this.host = host;
    this.port = port;
    this.sessionId = null;
  }

  async sendJsonRequest(message) {
    return new Promise((resolve, reject) => {
      const data = JSON.stringify(message);
      
      const options = {
        hostname: this.host,
        port: this.port,
        path: '/mcp',
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'MCP-Protocol-Version': '2025-06-18',
          'Content-Length': Buffer.byteLength(data),
          'Origin': `http://${this.host}:${this.port}`
        }
      };

      if (this.sessionId) {
        options.headers['Mcp-Session-Id'] = this.sessionId;
      }

      const req = http.request(options, (res) => {
        let responseData = '';

        // Capture session ID from response
        if (res.headers['mcp-session-id']) {
          this.sessionId = res.headers['mcp-session-id'];
        }

        res.on('data', (chunk) => {
          responseData += chunk;
        });

        res.on('end', () => {
          try {
            const response = JSON.parse(responseData);
            resolve(response);
          } catch (error) {
            reject(new Error(`Invalid JSON response: ${responseData}`));
          }
        });
      });

      req.on('error', (error) => {
        reject(error);
      });

      req.write(data);
      req.end();
    });
  }

  async sendSseRequest(message) {
    return new Promise((resolve, reject) => {
      const data = JSON.stringify(message);
      
      const options = {
        hostname: this.host,
        port: this.port,
        path: '/mcp',
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'text/event-stream',
          'MCP-Protocol-Version': '2025-06-18',
          'Content-Length': Buffer.byteLength(data),
          'Origin': `http://${this.host}:${this.port}`,
          'Cache-Control': 'no-cache'
        }
      };

      if (this.sessionId) {
        options.headers['Mcp-Session-Id'] = this.sessionId;
      }

      const req = http.request(options, (res) => {
        console.log(`SSE Response Status: ${res.statusCode}`);
        console.log(`Content-Type: ${res.headers['content-type']}`);

        // Capture session ID
        if (res.headers['mcp-session-id']) {
          this.sessionId = res.headers['mcp-session-id'];
        }

        res.setEncoding('utf8');
        
        let buffer = '';
        res.on('data', (chunk) => {
          buffer += chunk;
          
          // Process SSE events
          const lines = buffer.split('\n\n');
          buffer = lines.pop(); // Keep incomplete event in buffer
          
          for (const eventText of lines) {
            if (eventText.trim()) {
              this.parseSSEEvent(eventText);
            }
          }
        });

        res.on('end', () => {
          console.log('SSE stream ended');
          resolve();
        });
      });

      req.on('error', (error) => {
        reject(error);
      });

      req.write(data);
      req.end();
    });
  }

  parseSSEEvent(eventText) {
    const lines = eventText.split('\n');
    const event = {};
    
    for (const line of lines) {
      const colonIndex = line.indexOf(':');
      if (colonIndex === -1) continue;
      
      const field = line.substring(0, colonIndex).trim();
      const value = line.substring(colonIndex + 1).trim();
      
      if (field === 'data') {
        if (!event.data) event.data = [];
        event.data.push(value);
      } else {
        event[field] = value;
      }
    }
    
    if (event.data) {
      try {
        const jsonData = JSON.parse(event.data.join('\n'));
        console.log(`SSE Event [${event.event || 'message'}]:`, JSON.stringify(jsonData, null, 2));
      } catch (error) {
        console.log(`SSE Event [${event.event || 'message'}]: ${event.data.join('\n')}`);
      }
    }
  }

  async getHealth() {
    return new Promise((resolve, reject) => {
      const options = {
        hostname: this.host,
        port: this.port,
        path: '/health',
        method: 'GET'
      };

      const req = http.request(options, (res) => {
        let data = '';
        res.on('data', (chunk) => data += chunk);
        res.on('end', () => {
          try {
            resolve(JSON.parse(data));
          } catch (error) {
            reject(error);
          }
        });
      });

      req.on('error', reject);
      req.end();
    });
  }

  async getStatus() {
    return new Promise((resolve, reject) => {
      const options = {
        hostname: this.host,
        port: this.port,
        path: '/status',
        method: 'GET'
      };

      const req = http.request(options, (res) => {
        let data = '';
        res.on('data', (chunk) => data += chunk);
        res.on('end', () => {
          try {
            resolve(JSON.parse(data));
          } catch (error) {
            reject(error);
          }
        });
      });

      req.on('error', reject);
      req.end();
    });
  }
}

// Test scenarios
async function runTests() {
  console.log('Starting MCP Proxy Test Client...\n');
  
  const client = new McpTestClient();

  try {
    // Test 1: Health check
    console.log('=== Test 1: Health Check ===');
    const health = await client.getHealth();
    console.log('Health:', JSON.stringify(health, null, 2));
    console.log();

    // Test 2: Status check
    console.log('=== Test 2: Status Check ===');
    const status = await client.getStatus();
    console.log('Status:', JSON.stringify(status, null, 2));
    console.log();

    // Test 3: List servers (proxy-specific method)
    console.log('=== Test 3: List Servers ===');
    const listServersResponse = await client.sendJsonRequest({
      jsonrpc: '2.0',
      id: 1,
      method: 'proxy/listServers',
      params: {}
    });
    console.log('List Servers Response:', JSON.stringify(listServersResponse, null, 2));
    console.log();

    // Test 4: Initialize request (should be routed to first available server)
    console.log('=== Test 4: Initialize Request ===');
    const initResponse = await client.sendJsonRequest({
      jsonrpc: '2.0',
      id: 2,
      method: 'initialize',
      params: {
        protocolVersion: '2025-06-18',
        capabilities: {},
        clientInfo: {
          name: 'test-client',
          version: '1.0.0'
        }
      }
    });
    console.log('Initialize Response:', JSON.stringify(initResponse, null, 2));
    console.log();

    // Test 5: SSE streaming request
    console.log('=== Test 5: SSE Streaming Request ===');
    console.log('Sending SSE request (will show events as they arrive)...');
    await client.sendSseRequest({
      jsonrpc: '2.0',
      id: 3,
      method: 'proxy/getServerStatus',
      params: {}
    });
    console.log();

    console.log('All tests completed successfully!');

  } catch (error) {
    console.error('Test failed:', error.message);
    process.exit(1);
  }
}

// Parse command line arguments
const args = process.argv.slice(2);
let host = 'localhost';
let port = 3000;

for (let i = 0; i < args.length; i++) {
  if (args[i] === '--host' && args[i + 1]) {
    host = args[i + 1];
    i++;
  } else if (args[i] === '--port' && args[i + 1]) {
    port = parseInt(args[i + 1]);
    i++;
  } else if (args[i] === '--help') {
    console.log(`
Usage: node test-client.js [options]

Options:
  --host <host>    Proxy server host (default: localhost)
  --port <port>    Proxy server port (default: 3000)
  --help          Show this help message

Examples:
  node test-client.js
  node test-client.js --host 127.0.0.1 --port 8080
`);
    process.exit(0);
  }
}

if (require.main === module) {
  runTests().catch(console.error);
}