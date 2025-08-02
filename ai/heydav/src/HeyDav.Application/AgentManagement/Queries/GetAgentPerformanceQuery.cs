using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Services;

namespace HeyDav.Application.AgentManagement.Queries;

public record GetAgentPerformanceQuery(Guid AgentId) : IQuery<AgentPerformanceMetrics>;

public class GetAgentPerformanceQueryHandler(IAgentManager agentManager) 
    : IQueryHandler<GetAgentPerformanceQuery, AgentPerformanceMetrics>
{
    private readonly IAgentManager _agentManager = agentManager ?? throw new ArgumentNullException(nameof(agentManager));

    public async Task<AgentPerformanceMetrics> Handle(GetAgentPerformanceQuery request, CancellationToken cancellationToken)
    {
        return await _agentManager.GetAgentPerformanceMetricsAsync(request.AgentId, cancellationToken);
    }
}