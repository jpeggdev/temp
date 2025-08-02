using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Interfaces;
using HeyDav.Domain.AgentManagement.ValueObjects;

namespace HeyDav.Application.AgentManagement.Commands;

public record CreateAgentCommand(
    string Name,
    AgentType Type,
    string? Description = null,
    string ModelName = "gpt-4",
    int MaxTokens = 4000,
    double Temperature = 0.7,
    int MaxConcurrentTasks = 3,
    TimeSpan? TaskTimeout = null,
    Dictionary<string, string>? CustomSettings = null) : ICommand<Guid>;

public class CreateAgentCommandHandler(IAgentRepository repository) : ICommandHandler<CreateAgentCommand, Guid>
{
    private readonly IAgentRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<Guid> Handle(CreateAgentCommand request, CancellationToken cancellationToken)
    {
        var configuration = AgentConfiguration.Create(
            request.ModelName,
            request.MaxTokens,
            request.Temperature,
            request.MaxConcurrentTasks,
            request.TaskTimeout,
            request.CustomSettings);

        var agent = AIAgent.Create(
            request.Name,
            request.Type,
            configuration,
            request.Description);

        await _repository.AddAsync(agent, cancellationToken);
        
        return agent.Id;
    }
}