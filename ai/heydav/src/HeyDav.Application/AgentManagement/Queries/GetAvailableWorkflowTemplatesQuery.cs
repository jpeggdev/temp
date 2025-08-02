using HeyDav.Application.Common.Interfaces;
using HeyDav.Application.AgentManagement.Services;

namespace HeyDav.Application.AgentManagement.Queries;

public record GetAvailableWorkflowTemplatesQuery() : IQuery<IEnumerable<WorkflowTemplate>>;

public class GetAvailableWorkflowTemplatesQueryHandler(IAgentWorkflowEngine workflowEngine) 
    : IQueryHandler<GetAvailableWorkflowTemplatesQuery, IEnumerable<WorkflowTemplate>>
{
    private readonly IAgentWorkflowEngine _workflowEngine = workflowEngine ?? throw new ArgumentNullException(nameof(workflowEngine));

    public async Task<IEnumerable<WorkflowTemplate>> Handle(GetAvailableWorkflowTemplatesQuery request, CancellationToken cancellationToken)
    {
        return await _workflowEngine.GetAvailableTemplatesAsync(cancellationToken);
    }
}