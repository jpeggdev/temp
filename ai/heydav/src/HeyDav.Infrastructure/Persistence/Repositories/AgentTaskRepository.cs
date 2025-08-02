using Microsoft.EntityFrameworkCore;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Interfaces;

namespace HeyDav.Infrastructure.Persistence.Repositories;

public class AgentTaskRepository(HeyDavDbContext context) : Repository<AgentTask>(context), IAgentTaskRepository
{
    public async Task<IEnumerable<AgentTask>> GetPendingTasksAsync(CancellationToken cancellationToken = default)
    {
        return await _context.Set<AgentTask>()
            .Where(t => t.Status == AgentTaskStatus.Pending)
            .OrderBy(t => t.Priority)
            .ThenBy(t => t.CreatedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<AgentTask>> GetTasksByStatusAsync(AgentTaskStatus status, CancellationToken cancellationToken = default)
    {
        return await _context.Set<AgentTask>()
            .Where(t => t.Status == status)
            .OrderBy(t => t.CreatedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<AgentTask>> GetTasksByAgentAsync(Guid agentId, CancellationToken cancellationToken = default)
    {
        return await _context.Set<AgentTask>()
            .Where(t => t.AssignedAgentId == agentId)
            .OrderBy(t => t.CreatedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<AgentTask>> GetOverdueTasksAsync(CancellationToken cancellationToken = default)
    {
        var now = DateTime.UtcNow;
        
        return await _context.Set<AgentTask>()
            .Where(t => t.DueDate.HasValue && t.DueDate.Value < now)
            .Where(t => t.Status != AgentTaskStatus.Completed && t.Status != AgentTaskStatus.Cancelled)
            .OrderBy(t => t.DueDate)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<AgentTask>> GetTasksByPriorityAsync(TaskPriority priority, CancellationToken cancellationToken = default)
    {
        return await _context.Set<AgentTask>()
            .Where(t => t.Priority == priority)
            .OrderBy(t => t.CreatedAt)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<AgentTask>> GetRetryableTasksAsync(CancellationToken cancellationToken = default)
    {
        return await _context.Set<AgentTask>()
            .Where(t => t.Status == AgentTaskStatus.Failed)
            .Where(t => t.RetryCount < t.MaxRetries)
            .OrderBy(t => t.Priority)
            .ThenBy(t => t.CreatedAt)
            .ToListAsync(cancellationToken);
    }
}