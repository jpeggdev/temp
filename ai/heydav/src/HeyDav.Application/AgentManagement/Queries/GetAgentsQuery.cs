using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Interfaces;

namespace HeyDav.Application.AgentManagement.Queries;

public record GetAgentsQuery() : IQuery<IReadOnlyList<AIAgent>>;

public class GetAgentsQueryHandler(IAgentRepository repository) : IQueryHandler<GetAgentsQuery, IReadOnlyList<AIAgent>>
{
    private readonly IAgentRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<IReadOnlyList<AIAgent>> Handle(GetAgentsQuery request, CancellationToken cancellationToken)
    {
        var agents = await _repository.GetAllAsync(cancellationToken);
        return agents.ToList().AsReadOnly();
    }
}

public record GetActiveAgentsQuery() : IQuery<IReadOnlyList<AIAgent>>;

public class GetActiveAgentsQueryHandler(IAgentRepository repository)
    : IQueryHandler<GetActiveAgentsQuery, IReadOnlyList<AIAgent>>
{
    private readonly IAgentRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<IReadOnlyList<AIAgent>> Handle(GetActiveAgentsQuery request, CancellationToken cancellationToken)
    {
        var agents = await _repository.GetActiveAgentsAsync(cancellationToken);
        return agents.ToList().AsReadOnly();
    }
}

public record GetAgentsByTypeQuery(AgentType Type) : IQuery<IReadOnlyList<AIAgent>>;

public class GetAgentsByTypeQueryHandler(IAgentRepository repository)
    : IQueryHandler<GetAgentsByTypeQuery, IReadOnlyList<AIAgent>>
{
    private readonly IAgentRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<IReadOnlyList<AIAgent>> Handle(GetAgentsByTypeQuery request, CancellationToken cancellationToken)
    {
        var agents = await _repository.GetAgentsByTypeAsync(request.Type, cancellationToken);
        return agents.ToList().AsReadOnly();
    }
}