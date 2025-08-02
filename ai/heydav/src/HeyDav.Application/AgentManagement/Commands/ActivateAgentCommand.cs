using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Services;

namespace HeyDav.Application.AgentManagement.Commands;

public record ActivateAgentCommand(Guid AgentId) : ICommand<bool>;

public class ActivateAgentCommandHandler(IAgentManager agentManager) : ICommandHandler<ActivateAgentCommand, bool>
{
    private readonly IAgentManager _agentManager = agentManager ?? throw new ArgumentNullException(nameof(agentManager));

    public async Task<bool> Handle(ActivateAgentCommand request, CancellationToken cancellationToken)
    {
        return await _agentManager.ActivateAgentAsync(request.AgentId, cancellationToken);
    }
}