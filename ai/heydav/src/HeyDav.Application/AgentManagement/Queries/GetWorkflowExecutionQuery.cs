using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Services;

namespace HeyDav.Application.AgentManagement.Queries;

public record GetWorkflowExecutionQuery(Guid WorkflowId) : IQuery<WorkflowExecution?>;

public class GetWorkflowExecutionQueryHandler(IAgentWorkflowEngine workflowEngine) 
    : IQueryHandler<GetWorkflowExecutionQuery, WorkflowExecution?>
{
    private readonly IAgentWorkflowEngine _workflowEngine = workflowEngine ?? throw new ArgumentNullException(nameof(workflowEngine));

    public async Task<WorkflowExecution?> Handle(GetWorkflowExecutionQuery request, CancellationToken cancellationToken)
    {
        return await _workflowEngine.GetWorkflowExecutionAsync(request.WorkflowId, cancellationToken);
    }
}