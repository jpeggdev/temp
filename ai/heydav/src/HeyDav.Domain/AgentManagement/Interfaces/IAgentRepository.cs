using HeyDav.Domain.Common.Interfaces;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;

namespace HeyDav.Domain.AgentManagement.Interfaces;

public interface IAgentRepository : IRepository<AIAgent>
{
    Task<IEnumerable<AIAgent>> GetActiveAgentsAsync(CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetAgentsByTypeAsync(AgentType type, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetAvailableAgentsAsync(CancellationToken cancellationToken = default);
    Task<AIAgent?> GetAgentWithCapabilityAsync(string capability, CancellationToken cancellationToken = default);
    Task<IEnumerable<AIAgent>> GetAgentsWithCapabilitiesAsync(IEnumerable<string> capabilities, CancellationToken cancellationToken = default);
    Task<AIAgent?> GetBestAgentForTaskAsync(AgentTask task, CancellationToken cancellationToken = default);
}