using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Services;

namespace HeyDav.Application.AgentManagement.Commands;

public record StartWorkflowCommand(
    Guid WorkflowId,
    Dictionary<string, object>? InitialData = null) : ICommand<bool>;

public class StartWorkflowCommandHandler(IAgentWorkflowEngine workflowEngine) : ICommandHandler<StartWorkflowCommand, bool>
{
    private readonly IAgentWorkflowEngine _workflowEngine = workflowEngine ?? throw new ArgumentNullException(nameof(workflowEngine));

    public async Task<bool> Handle(StartWorkflowCommand request, CancellationToken cancellationToken)
    {
        return await _workflowEngine.StartWorkflowAsync(request.WorkflowId, request.InitialData, cancellationToken);
    }
}