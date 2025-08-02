using HeyDav.Domain.Common.Interfaces;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;

namespace HeyDav.Domain.AgentManagement.Interfaces;

public interface IAgentTaskRepository : IRepository<AgentTask>
{
    Task<IEnumerable<AgentTask>> GetPendingTasksAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentTask>> GetTasksByStatusAsync(AgentTaskStatus status, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentTask>> GetTasksByAgentAsync(Guid agentId, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentTask>> GetOverdueTasksAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentTask>> GetTasksByPriorityAsync(TaskPriority priority, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentTask>> GetRetryableTasksAsync(CancellationToken cancellationToken = default);
}