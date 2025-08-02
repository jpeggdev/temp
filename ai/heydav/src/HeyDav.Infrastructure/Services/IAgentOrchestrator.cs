using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;

namespace HeyDav.Infrastructure.Services;

public interface IAgentOrchestrator
{
    Task<Guid?> AssignTaskAsync(AgentTask task, CancellationToken cancellationToken = default);
    Task<AIAgent?> FindBestAgentAsync(AgentTask task, CancellationToken cancellationToken = default);
    Task<bool> ExecuteTaskAsync(Guid taskId, CancellationToken cancellationToken = default);
    Task<IEnumerable<AgentTask>> GetPendingTasksAsync(CancellationToken cancellationToken = default);
    Task ProcessPendingTasksAsync(CancellationToken cancellationToken = default);
    Task<bool> RetryFailedTaskAsync(Guid taskId, CancellationToken cancellationToken = default);
    Task MonitorAgentHealthAsync(CancellationToken cancellationToken = default);
}