using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Enums;
using HeyDav.Domain.AgentManagement.Interfaces;

namespace HeyDav.Application.AgentManagement.Commands;

public record CreateAgentTaskCommand(
    string Title,
    TaskPriority Priority = TaskPriority.Normal,
    string? Description = null,
    DateTime? DueDate = null,
    int MaxRetries = 3,
    List<string>? RequiredCapabilities = null,
    Dictionary<string, object>? Parameters = null) : ICommand<Guid>;

public class CreateAgentTaskCommandHandler(IAgentTaskRepository repository)
    : ICommandHandler<CreateAgentTaskCommand, Guid>
{
    private readonly IAgentTaskRepository _repository = repository ?? throw new ArgumentNullException(nameof(repository));

    public async Task<Guid> Handle(CreateAgentTaskCommand request, CancellationToken cancellationToken)
    {
        var task = AgentTask.Create(
            request.Title,
            request.Priority,
            request.Description,
            request.DueDate,
            request.MaxRetries);

        if (request.RequiredCapabilities != null)
        {
            foreach (var capability in request.RequiredCapabilities)
            {
                task.AddRequiredCapability(capability);
            }
        }

        if (request.Parameters != null)
        {
            foreach (var parameter in request.Parameters)
            {
                task.AddParameter(parameter.Key, parameter.Value);
            }
        }

        await _repository.AddAsync(task, cancellationToken);
        
        return task.Id;
    }
}