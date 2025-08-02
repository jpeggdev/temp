using Microsoft.EntityFrameworkCore;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Interfaces;

namespace HeyDav.Infrastructure.Persistence.Repositories;

public class McpServerRepository(HeyDavDbContext context) : Repository<McpServer>(context), IMapServerRepository
{
    public async Task<IEnumerable<McpServer>> GetActiveServersAsync(CancellationToken cancellationToken = default)
    {
        return await _context.Set<McpServer>()
            .Where(s => s.IsActive)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<McpServer>> GetServersWithToolAsync(string tool, CancellationToken cancellationToken = default)
    {
        return await _context.Set<McpServer>()
            .Where(s => s.IsActive && s.SupportedTools.Contains(tool))
            .ToListAsync(cancellationToken);
    }

    public async Task<McpServer?> GetServerByNameAsync(string name, CancellationToken cancellationToken = default)
    {
        return await _context.Set<McpServer>()
            .FirstOrDefaultAsync(s => s.Name == name, cancellationToken);
    }

    public async Task<IEnumerable<McpServer>> GetHealthyServersAsync(CancellationToken cancellationToken = default)
    {
        return await _context.Set<McpServer>()
            .Where(s => s.IsActive && s.LastError == null)
            .ToListAsync(cancellationToken);
    }
}