using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Services;

namespace HeyDav.Application.AgentManagement.Commands;

public record DeactivateAgentCommand(Guid AgentId) : ICommand<bool>;

public class DeactivateAgentCommandHandler(IAgentManager agentManager) : ICommandHandler<DeactivateAgentCommand, bool>
{
    private readonly IAgentManager _agentManager = agentManager ?? throw new ArgumentNullException(nameof(agentManager));

    public async Task<bool> Handle(DeactivateAgentCommand request, CancellationToken cancellationToken)
    {
        return await _agentManager.DeactivateAgentAsync(request.AgentId, cancellationToken);
    }
}