using HeyDav.Domain.AgentManagement.Entities;

namespace HeyDav.Infrastructure.Services;

public interface IMcpClient
{
    Task<bool> ConnectAsync(McpServer server, CancellationToken cancellationToken = default);
    Task DisconnectAsync(Guid serverId, CancellationToken cancellationToken = default);
    Task<bool> IsHealthyAsync(Guid serverId, CancellationToken cancellationToken = default);
    Task<IEnumerable<string>> GetAvailableToolsAsync(Guid serverId, CancellationToken cancellationToken = default);
    Task<T?> ExecuteToolAsync<T>(Guid serverId, string toolName, Dictionary<string, object> parameters, CancellationToken cancellationToken = default);
    Task<string?> ExecuteToolAsync(Guid serverId, string toolName, Dictionary<string, object> parameters, CancellationToken cancellationToken = default);
    Task<Dictionary<string, object>?> GetServerInfoAsync(Guid serverId, CancellationToken cancellationToken = default);
}