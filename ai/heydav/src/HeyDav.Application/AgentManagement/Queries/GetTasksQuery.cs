using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Interfaces;

namespace HeyDav.Application.AgentManagement.Queries;

public record GetPendingTasksQuery() : IQuery<IReadOnlyList<AgentTask>>;

public class GetPendingTasksQueryHandler(IAgentTaskRepository repository)
    : IQueryHandler<GetPendingTasksQuery, IReadOnlyList<AgentTask>>
{
    private readonly IAgentTaskRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<IReadOnlyList<AgentTask>> Handle(GetPendingTasksQuery request, CancellationToken cancellationToken)
    {
        var tasks = await _repository.GetPendingTasksAsync(cancellationToken);
        return tasks.ToList().AsReadOnly();
    }
}

public record GetTasksByAgentQuery(Guid AgentId) : IQuery<IReadOnlyList<AgentTask>>;

public class GetTasksByAgentQueryHandler(IAgentTaskRepository repository)
    : IQueryHandler<GetTasksByAgentQuery, IReadOnlyList<AgentTask>>
{
    private readonly IAgentTaskRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<IReadOnlyList<AgentTask>> Handle(GetTasksByAgentQuery request, CancellationToken cancellationToken)
    {
        var tasks = await _repository.GetTasksByAgentAsync(request.AgentId, cancellationToken);
        return tasks.ToList().AsReadOnly();
    }
}

public record GetOverdueTasksQuery() : IQuery<IReadOnlyList<AgentTask>>;

public class GetOverdueTasksQueryHandler(IAgentTaskRepository repository)
    : IQueryHandler<GetOverdueTasksQuery, IReadOnlyList<AgentTask>>
{
    private readonly IAgentTaskRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<IReadOnlyList<AgentTask>> Handle(GetOverdueTasksQuery request, CancellationToken cancellationToken)
    {
        var tasks = await _repository.GetOverdueTasksAsync(cancellationToken);
        return tasks.ToList().AsReadOnly();
    }
}