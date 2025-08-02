using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Services;
using HeyDav.Domain.AgentManagement.Entities;

namespace HeyDav.Application.AgentManagement.Queries;

public record FindBestAgentForTaskQuery(
    string TaskDescription,
    string? Domain = null,
    string? Subdomain = null,
    IEnumerable<string>? Keywords = null) : IQuery<AIAgent?>;

public class FindBestAgentForTaskQueryHandler(IAgentManager agentManager) 
    : IQueryHandler<FindBestAgentForTaskQuery, AIAgent?>
{
    private readonly IAgentManager _agentManager = agentManager ?? throw new ArgumentNullException(nameof(agentManager));

    public async Task<AIAgent?> Handle(FindBestAgentForTaskQuery request, CancellationToken cancellationToken)
    {
        return await _agentManager.FindBestAgentForTaskAsync(
            request.TaskDescription,
            request.Domain,
            request.Subdomain,
            request.Keywords,
            cancellationToken);
    }
}