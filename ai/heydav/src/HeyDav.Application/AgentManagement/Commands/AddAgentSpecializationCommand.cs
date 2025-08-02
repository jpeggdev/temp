using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Services;
using HeyDav.Domain.AgentManagement.ValueObjects;

namespace HeyDav.Application.AgentManagement.Commands;

public record AddAgentSpecializationCommand(
    Guid AgentId,
    string Domain,
    string Subdomain,
    int SkillLevel = 5,
    double Confidence = 0.5,
    List<string>? Keywords = null) : ICommand<bool>;

public class AddAgentSpecializationCommandHandler(IAgentManager agentManager) : ICommandHandler<AddAgentSpecializationCommand, bool>
{
    private readonly IAgentManager _agentManager = agentManager ?? throw new ArgumentNullException(nameof(agentManager));

    public async Task<bool> Handle(AddAgentSpecializationCommand request, CancellationToken cancellationToken)
    {
        var specialization = AgentSpecialization.Create(
            request.Domain,
            request.Subdomain,
            request.SkillLevel,
            request.Confidence,
            request.Keywords
        );

        return await _agentManager.AddAgentSpecializationAsync(request.AgentId, specialization, cancellationToken);
    }
}