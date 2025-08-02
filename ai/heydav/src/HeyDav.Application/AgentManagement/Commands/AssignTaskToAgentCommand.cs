using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.AgentManagement.Interfaces;

namespace HeyDav.Application.AgentManagement.Commands;

public record AssignTaskToAgentCommand(Guid TaskId, Guid AgentId) : ICommand<bool>;

public class AssignTaskToAgentCommandHandler(
    IAgentRepository agentRepository,
    IAgentTaskRepository taskRepository)
    : ICommandHandler<AssignTaskToAgentCommand, bool>
{
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly IAgentTaskRepository _taskRepository = taskRepository ?? throw new ArgumentNullException(nameof(taskRepository));

    public async Task<bool> Handle(AssignTaskToAgentCommand request, CancellationToken cancellationToken)
    {
        var agent = await _agentRepository.GetByIdAsync(request.AgentId, cancellationToken);
        if (agent == null || !agent.CanAcceptTask())
        {
            return false;
        }

        var task = await _taskRepository.GetByIdAsync(request.TaskId, cancellationToken);
        if (task == null)
        {
            return false;
        }

        // Check if agent has required capabilities
        if (task.RequiredCapabilities.Any(capability => !agent.HasCapability(capability)))
        {
            return false;
        }

        agent.AssignTask(task);
        await _agentRepository.UpdateAsync(agent, cancellationToken);
        
        return true;
    }
}