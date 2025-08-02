using Microsoft.EntityFrameworkCore;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Interfaces;

namespace HeyDav.Infrastructure.Persistence.Repositories;

public class AgentRepository(HeyDavDbContext context) : Repository<AIAgent>(context), IAgentRepository
{
    public async Task<IEnumerable<AIAgent>> GetActiveAgentsAsync(CancellationToken cancellationToken = default)
    {
        return await _context.Set<AIAgent>()
            .Where(a => a.Status == AgentStatus.Active)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<AIAgent>> GetAgentsByTypeAsync(AgentType type, CancellationToken cancellationToken = default)
    {
        return await _context.Set<AIAgent>()
            .Where(a => a.Type == type)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<AIAgent>> GetAvailableAgentsAsync(CancellationToken cancellationToken = default)
    {
        return await _context.Set<AIAgent>()
            .Where(a => a.Status == AgentStatus.Active)
            .Where(a => a.CurrentTasks.Count < a.Configuration.MaxConcurrentTasks)
            .ToListAsync(cancellationToken);
    }

    public async Task<AIAgent?> GetAgentWithCapabilityAsync(string capability, CancellationToken cancellationToken = default)
    {
        return await _context.Set<AIAgent>()
            .Where(a => a.Status == AgentStatus.Active)
            .Where(a => a.Capabilities.Contains(capability))
            .FirstOrDefaultAsync(cancellationToken);
    }

    public async Task<IEnumerable<AIAgent>> GetAgentsWithCapabilitiesAsync(IEnumerable<string> capabilities, CancellationToken cancellationToken = default)
    {
        var capabilityList = capabilities.ToList();
        
        return await _context.Set<AIAgent>()
            .Where(a => a.Status == AgentStatus.Active)
            .Where(a => capabilityList.All(cap => a.Capabilities.Contains(cap)))
            .ToListAsync(cancellationToken);
    }

    public async Task<AIAgent?> GetBestAgentForTaskAsync(AgentTask task, CancellationToken cancellationToken = default)
    {
        var query = _context.Set<AIAgent>()
            .Where(a => a.Status == AgentStatus.Active)
            .Where(a => a.CurrentTasks.Count < a.Configuration.MaxConcurrentTasks);

        // Filter by required capabilities
        if (task.RequiredCapabilities.Any())
        {
            query = query.Where(a => task.RequiredCapabilities.All(cap => a.Capabilities.Contains(cap)));
        }

        // Order by success rate, then by current load, then by average response time
        return await query
            .OrderByDescending(a => a.GetSuccessRate())
            .ThenBy(a => a.CurrentTasks.Count)
            .ThenBy(a => a.AverageResponseTime)
            .FirstOrDefaultAsync(cancellationToken);
    }
}