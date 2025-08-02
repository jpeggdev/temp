using HeyDav.Domain.Common.Interfaces;
using HeyDav.Domain.AgentManagement.Entities;

namespace HeyDav.Domain.AgentManagement.Interfaces;

public interface IMapServerRepository : IRepository<McpServer>
{
    Task<IEnumerable<McpServer>> GetActiveServersAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<McpServer>> GetServersWithToolAsync(string tool, CancellationToken cancellationToken = default);
    Task<McpServer?> GetServerByNameAsync(string name, CancellationToken cancellationToken = default);
    Task<IEnumerable<McpServer>> GetHealthyServersAsync(CancellationToken cancellationToken = default);
}