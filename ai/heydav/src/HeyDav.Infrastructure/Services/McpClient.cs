using System.Collections.Concurrent;
using System.Net.Http;
using System.Text.Json;
using HeyDav.Domain.AgentManagement.Entities;
using Microsoft.Extensions.Logging;

namespace HeyDav.Infrastructure.Services;

public class McpClient(HttpClient httpClient, ILogger<McpClient> logger) : IMcpClient
{
    private readonly HttpClient _httpClient = httpClient ?? throw new ArgumentNullException(nameof(httpClient));
    private readonly ILogger<McpClient> _logger = logger ?? throw new ArgumentNullException(nameof(logger));
    private readonly ConcurrentDictionary<Guid, McpServerConnection> _connections = new();

    public async Task<bool> ConnectAsync(McpServer server, CancellationToken cancellationToken = default)
    {
        try
        {
            var connectionString = server.Endpoint.GetConnectionString();
            _logger.LogInformation("Attempting to connect to MCP server {ServerName} at {ConnectionString}", 
                server.Name, connectionString);

            // Create HTTP client for this server if needed
            var client = new HttpClient();
            
            // Add custom headers if specified
            foreach (var header in server.Endpoint.Headers)
            {
                client.DefaultRequestHeaders.Add(header.Key, header.Value);
            }

            // Test connection with a simple ping/info request
            var response = await client.GetAsync($"{connectionString}/info", cancellationToken);
            
            if (response.IsSuccessStatusCode)
            {
                var content = await response.Content.ReadAsStringAsync(cancellationToken);
                var serverInfo = JsonSerializer.Deserialize<Dictionary<string, object>>(content);
                
                var connection = new McpServerConnection
                {
                    ServerId = server.Id,
                    Client = client,
                    ConnectionString = connectionString,
                    IsConnected = true,
                    LastConnectedAt = DateTime.UtcNow,
                    ServerInfo = serverInfo ?? new Dictionary<string, object>()
                };

                _connections.AddOrUpdate(server.Id, connection, (key, oldValue) => connection);
                
                _logger.LogInformation("Successfully connected to MCP server {ServerName}", server.Name);
                return true;
            }
            else
            {
                _logger.LogWarning("Failed to connect to MCP server {ServerName}. Status: {StatusCode}", 
                    server.Name, response.StatusCode);
                client.Dispose();
                return false;
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error connecting to MCP server {ServerName}", server.Name);
            return false;
        }
    }

    public async Task DisconnectAsync(Guid serverId, CancellationToken cancellationToken = default)
    {
        if (_connections.TryRemove(serverId, out var connection))
        {
            connection.Client?.Dispose();
            _logger.LogInformation("Disconnected from MCP server {ServerId}", serverId);
        }
        
        await Task.CompletedTask;
    }

    public async Task<bool> IsHealthyAsync(Guid serverId, CancellationToken cancellationToken = default)
    {
        if (!_connections.TryGetValue(serverId, out var connection) || !connection.IsConnected)
        {
            return false;
        }

        try
        {
            var response = await connection.Client!.GetAsync($"{connection.ConnectionString}/health", cancellationToken);
            return response.IsSuccessStatusCode;
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Health check failed for MCP server {ServerId}", serverId);
            return false;
        }
    }

    public async Task<IEnumerable<string>> GetAvailableToolsAsync(Guid serverId, CancellationToken cancellationToken = default)
    {
        if (!_connections.TryGetValue(serverId, out var connection) || !connection.IsConnected)
        {
            return Enumerable.Empty<string>();
        }

        try
        {
            var response = await connection.Client!.GetAsync($"{connection.ConnectionString}/tools", cancellationToken);
            
            if (response.IsSuccessStatusCode)
            {
                var content = await response.Content.ReadAsStringAsync(cancellationToken);
                var tools = JsonSerializer.Deserialize<string[]>(content);
                return tools ?? Enumerable.Empty<string>();
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get available tools from MCP server {ServerId}", serverId);
        }

        return Enumerable.Empty<string>();
    }

    public async Task<T?> ExecuteToolAsync<T>(Guid serverId, string toolName, Dictionary<string, object> parameters, CancellationToken cancellationToken = default)
    {
        var result = await ExecuteToolAsync(serverId, toolName, parameters, cancellationToken);
        
        if (result != null)
        {
            try
            {
                return JsonSerializer.Deserialize<T>(result);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to deserialize tool result to type {Type}", typeof(T).Name);
            }
        }

        return default(T);
    }

    public async Task<string?> ExecuteToolAsync(Guid serverId, string toolName, Dictionary<string, object> parameters, CancellationToken cancellationToken = default)
    {
        if (!_connections.TryGetValue(serverId, out var connection) || !connection.IsConnected)
        {
            _logger.LogWarning("MCP server {ServerId} is not connected", serverId);
            return null;
        }

        try
        {
            var request = new
            {
                tool = toolName,
                parameters = parameters
            };

            var json = JsonSerializer.Serialize(request);
            var content = new StringContent(json, System.Text.Encoding.UTF8, "application/json");

            var response = await connection.Client!.PostAsync($"{connection.ConnectionString}/execute", content, cancellationToken);
            
            if (response.IsSuccessStatusCode)
            {
                var result = await response.Content.ReadAsStringAsync(cancellationToken);
                _logger.LogDebug("Successfully executed tool {ToolName} on MCP server {ServerId}", toolName, serverId);
                return result;
            }
            else
            {
                _logger.LogWarning("Tool execution failed on MCP server {ServerId}. Tool: {ToolName}, Status: {StatusCode}", 
                    serverId, toolName, response.StatusCode);
                return null;
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing tool {ToolName} on MCP server {ServerId}", toolName, serverId);
            return null;
        }
    }

    public async Task<Dictionary<string, object>?> GetServerInfoAsync(Guid serverId, CancellationToken cancellationToken = default)
    {
        if (_connections.TryGetValue(serverId, out var connection))
        {
            return connection.ServerInfo;
        }

        return null;
    }

    private class McpServerConnection
    {
        public Guid ServerId { get; set; }
        public HttpClient? Client { get; set; }
        public string ConnectionString { get; set; } = string.Empty;
        public bool IsConnected { get; set; }
        public DateTime LastConnectedAt { get; set; }
        public Dictionary<string, object> ServerInfo { get; set; } = new();
    }
}